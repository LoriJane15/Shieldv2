<?php

namespace App\Http\Controllers\Japic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Japic\DecideAuthenticationRequest;
use App\Models\EclipAuthenticationRequest;
use App\Services\EclipAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthenticationController extends Controller
{
    public function index(Request $request): View
    {
        $requests = EclipAuthenticationRequest::query()
            ->where('assigned_to', $request->user()->id)
            ->with([
                'eclipCase.formerRebel:id,classified_id',
                'eclipCase.workflowActivities' => fn ($query) => $query->where('step_code', '4A')->with('documents'),
                'histories.user',
            ])
            ->latest('requested_at')->paginate(20);

        return view('japic.authentication.index', compact('requests'));
    }

    public function start(Request $request, EclipAuthenticationRequest $authentication, EclipAuthenticationService $service): RedirectResponse
    {
        $service->start($authentication, $request->user(), $request->ip());

        return back()->with('success', 'Authentication review started.');
    }

    public function decide(DecideAuthenticationRequest $request, EclipAuthenticationRequest $authentication, EclipAuthenticationService $service): RedirectResponse
    {
        $service->decide(
            $authentication, $request->user(), $request->validated('decision'),
            $request->validated('certification_reference'), $request->validated('remarks'), $request->ip(),
        );

        return back()->with('success', 'Authentication decision recorded.');
    }
}
