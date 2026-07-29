<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreAgencyRequest;
use App\Http\Requests\SuperAdmin\UpdateAgencyRequest;
use App\Models\GovAgency;
use App\Services\AgencyLogoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgencyController extends Controller
{
    public function __construct(private readonly AgencyLogoService $agencyLogos) {}

    public function index(Request $request): View
    {
        return view('super_admin.agencies.index', [
            'agencies' => GovAgency::withCount(['users', 'responses', 'taggings'])
                ->when($request->search, fn ($q, $s) => $q->where(
                    fn ($search) => $search
                        ->where('acronym', 'like', "%{$s}%")
                        ->orWhere('name', 'like', "%{$s}%")
                ))
                ->orderBy('acronym')->paginate(15)->withQueryString(),
        ]);
    }

    public function store(StoreAgencyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $storedLogo = null;

        if ($request->hasFile('profile')) {
            $storedLogo = $this->agencyLogos->store($request->file('profile'));
            $data['profile'] = $storedLogo;
        }

        try {
            GovAgency::create($data);
        } catch (\Throwable $exception) {
            $this->agencyLogos->delete($storedLogo);

            throw $exception;
        }

        return back()->with('success', "Agency {$data['acronym']} added.");
    }

    public function update(UpdateAgencyRequest $request, GovAgency $agency): RedirectResponse
    {
        $data = $request->validated();
        $oldLogo = $agency->profile;
        $storedLogo = null;

        if ($request->hasFile('profile')) {
            $storedLogo = $this->agencyLogos->store($request->file('profile'));
            $data['profile'] = $storedLogo;
        } else {
            unset($data['profile']);
        }

        try {
            $agency->update($data);
        } catch (\Throwable $exception) {
            $this->agencyLogos->delete($storedLogo);

            throw $exception;
        }

        if ($storedLogo) {
            $this->agencyLogos->delete($oldLogo);
        }

        return back()->with('success', 'Agency updated.');
    }

    public function destroy(GovAgency $agency): RedirectResponse
    {
        abort_if(
            $agency->users()->exists() || $agency->responses()->exists() || $agency->taggings()->exists(),
            422,
            'This agency has assigned users or workflow history and cannot be deleted.'
        );
        $logo = $agency->profile;
        $agency->delete();
        $this->agencyLogos->delete($logo);

        return back()->with('success', 'Agency deleted.');
    }
}
