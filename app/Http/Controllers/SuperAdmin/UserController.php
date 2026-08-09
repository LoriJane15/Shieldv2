<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreUserRequest;
use App\Http\Requests\SuperAdmin\UpdateUserRequest;
use App\Models\GovAgency;
use App\Models\Municipality;
use App\Models\User;
use App\Services\UserLogoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserLogoService $userLogos) {}

    public function index(Request $request): View
    {
        $users = User::query()
            ->with(['municipality', 'govAgency'])
            ->withCount(['rcspForms', 'implementations'])
            ->when($request->search, fn ($q, $s) => $q->where(
                fn ($search) => $search
                    ->where('name', 'like', "%{$s}%")
                    ->orWhere('username', 'like', "%{$s}%")
            ))
            ->when($request->role, fn ($q, $r) => $q->where('role', $r))
            ->orderBy('role')->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('super_admin.users.index', [
            'users' => $users,
            'roles' => config('shield.roles'),
            'municipalities' => Municipality::orderBy('name')->get(),
            'agencies' => GovAgency::orderBy('acronym')->get(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data = $this->scopeRoleFields($data);
        $storedLogo = null;

        if ($request->hasFile('logo')) {
            $storedLogo = $this->userLogos->store($request->file('logo'));
            $data['logo'] = $storedLogo;
        }

        try {
            User::create($data); // password auto-hashed via model cast
        } catch (\Throwable $exception) {
            $this->userLogos->delete($storedLogo);

            throw $exception;
        }

        return back()->with('success', "User {$data['username']} created.");
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active')
            ? $request->boolean('is_active')
            : $user->is_active;
        abort_if(
            $user->is($request->user()) && $data['role'] !== 'super_admin',
            422,
            'You cannot remove your own Super Admin role.'
        );
        abort_if(
            $user->is($request->user()) && ! $request->boolean('is_active'),
            422,
            'You cannot deactivate your own account.'
        );
        $data = $this->scopeRoleFields($data);
        $passwordChanged = ! empty($data['password']);

        if (! $passwordChanged) {
            unset($data['password']);   // keep existing
        }

        $oldLogo = $user->logo;
        $storedLogo = null;

        if ($request->hasFile('logo')) {
            $storedLogo = $this->userLogos->store($request->file('logo'));
            $data['logo'] = $storedLogo;
        } else {
            unset($data['logo']);
        }

        try {
            $user->update($data);
        } catch (\Throwable $exception) {
            $this->userLogos->delete($storedLogo);

            throw $exception;
        }

        if ($storedLogo) {
            $this->userLogos->delete($oldLogo);
        }
        if ($passwordChanged || ! $user->is_active) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        return back()->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot delete your own account.');
        abort_if(
            $user->rcspForms()->exists()
                || $user->implementations()->exists()
                || $user->createdEclipCases()->exists()
                || $user->eclipEligibilityReviews()->exists()
                || $user->eclipStatusHistories()->exists()
                || $user->eclipDocumentVersions()->exists()
                || $user->eclipDocumentReviews()->exists()
                || $user->eclipDocumentRequirementHistories()->exists()
                || $user->eclipAssistanceRequests()->exists()
                || $user->eclipAssistanceRevisions()->exists()
                || $user->eclipAssistanceCategoryHistories()->exists()
                || $user->eclipDilgReviews()->exists()
                || $user->eclipFundTransactions()->exists()
                || $user->eclipAssistanceReleases()->exists()
                || $user->eclipReportExports()->exists(),
            422,
            'This user owns workflow records and cannot be deleted.'
        );
        $logo = $user->logo;
        $user->notifications()->delete();
        $user->delete();
        $this->userLogos->delete($logo);

        return back()->with('success', 'User deleted.');
    }

    /** Null out role-scoped FKs that don't apply to the chosen role. */
    private function scopeRoleFields(array $data): array
    {
        if (! in_array(($data['role'] ?? null), ['lgu', 'lswdo', 'dilg_provincial_focal', 'local_eclip_committee'], true)) {
            $data['municipality_id'] = null;
        }
        if (($data['role'] ?? null) !== 'gov_agency') {
            $data['gov_agency_id'] = null;
        }

        return $data;
    }
}
