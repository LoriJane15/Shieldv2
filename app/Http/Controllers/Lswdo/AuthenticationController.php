<?php

namespace App\Http\Controllers\Lswdo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lswdo\RequestAuthenticationRequest;
use App\Models\EclipCase;
use App\Models\User;
use App\Services\EclipAuthenticationService;
use Illuminate\Http\RedirectResponse;

class AuthenticationController extends Controller
{
    public function store(RequestAuthenticationRequest $request, EclipCase $eclipCase, EclipAuthenticationService $authentication): RedirectResponse
    {
        $japic = User::query()->where('role', 'japic')->where('is_active', true)->findOrFail($request->integer('assigned_to'));
        $authentication->request($eclipCase, $japic, $request->user(), $request->ip());

        return back()->with('success', 'Authentication request assigned to JAPIC.');
    }
}
