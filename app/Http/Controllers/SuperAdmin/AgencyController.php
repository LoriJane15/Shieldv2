<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreAgencyRequest;
use App\Http\Requests\SuperAdmin\UpdateAgencyRequest;
use App\Models\GovAgency;
use App\Services\AgencyManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgencyController extends Controller
{
    public function __construct(private readonly AgencyManagementService $agencies) {}

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
        $this->agencies->create($data, $request->file('profile'), $request->user(), $request->ip(), $request->userAgent());

        return back()->with('success', "Agency {$data['acronym']} added.");
    }

    public function update(UpdateAgencyRequest $request, GovAgency $agency): RedirectResponse
    {
        $this->agencies->update($agency, $request->validated(), $request->file('profile'), $request->user(), $request->ip(), $request->userAgent());

        return back()->with('success', 'Agency updated.');
    }

    public function destroy(Request $request, GovAgency $agency): RedirectResponse
    {
        abort_if(
            $agency->users()->exists() || $agency->responses()->exists() || $agency->taggings()->exists(),
            422,
            'This agency has assigned users or workflow history and cannot be deleted.'
        );
        $this->agencies->delete($agency, $request->user(), $request->ip(), $request->userAgent());

        return back()->with('success', 'Agency deleted.');
    }
}
