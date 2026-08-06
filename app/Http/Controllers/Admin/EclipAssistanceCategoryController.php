<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEclipAssistanceCategoryRequest;
use App\Models\EclipAssistanceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EclipAssistanceCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.eclip-assistance-categories.index', [
            'categories' => EclipAssistanceCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreEclipAssistanceCategoryRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $category = EclipAssistanceCategory::query()->create([...$request->validated(), 'is_active' => true]);
            $category->histories()->create([
                'user_id' => $request->user()->id, 'action' => 'created',
                'new_values' => $category->only(['code', 'name', 'description', 'is_active', 'sort_order']),
            ]);
        });

        return back()->with('success', 'E-CLIP assistance category added.');
    }

    public function toggle(Request $request, EclipAssistanceCategory $category): RedirectResponse
    {
        DB::transaction(function () use ($request, $category) {
            $category->update(['is_active' => ! $category->is_active]);
            $category->histories()->create([
                'user_id' => $request->user()->id,
                'action' => $category->is_active ? 'activated' : 'deactivated',
                'new_values' => ['is_active' => $category->is_active],
            ]);
        });

        return back()->with('success', 'Assistance category status updated.');
    }
}
