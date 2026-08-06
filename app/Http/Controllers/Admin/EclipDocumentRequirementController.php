<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEclipDocumentRequirementRequest;
use App\Models\EclipDocumentRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EclipDocumentRequirementController extends Controller
{
    public function index(): View
    {
        return view('admin.eclip-requirements.index', [
            'requirements' => EclipDocumentRequirement::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreEclipDocumentRequirementRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $requirement = EclipDocumentRequirement::query()->create([...$request->validated(), 'is_active' => true]);
            $requirement->histories()->create([
                'user_id' => $request->user()->id,
                'action' => 'created',
                'new_values' => $requirement->only(['code', 'name', 'description', 'is_required', 'is_active', 'sort_order']),
            ]);
        });

        return back()->with('success', 'E-CLIP document requirement added.');
    }

    public function toggle(Request $request, EclipDocumentRequirement $requirement): RedirectResponse
    {
        DB::transaction(function () use ($request, $requirement) {
            $requirement->update(['is_active' => ! $requirement->is_active]);
            $requirement->histories()->create([
                'user_id' => $request->user()->id,
                'action' => $requirement->is_active ? 'activated' : 'deactivated',
                'new_values' => ['is_active' => $requirement->is_active],
            ]);
        });

        return back()->with('success', 'Document requirement status updated.');
    }
}
