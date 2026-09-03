<?php

namespace App\Http\Controllers\Ib39;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ib39\IndexFeaProcessingRequest;
use App\Models\Ib39FeaProcessing;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FeaProcessingController extends Controller
{
    public function index(IndexFeaProcessingRequest $request): View
    {
        $processings = Ib39FeaProcessing::query()
            ->whereHas('surfacedFormerRebel')
            ->with([
                'surfacedFormerRebel.municipality',
                'surfacedFormerRebel.barangay',
                'documents',
            ])
            ->latest('id')
            ->paginate(20);

        return view('ib39.fea.index', compact('processings'));
    }

    public function show(Ib39FeaProcessing $fea): View
    {
        Gate::authorize('view', $fea);

        $fea->load([
            'surfacedFormerRebel.municipality',
            'surfacedFormerRebel.barangay',
            'documents' => fn ($query) => $query->with([
                'preparer:id,name',
                'lastUpdater:id,name',
                'histories' => fn ($history) => $history->with('actor:id,name')->oldest('created_at')->oldest('id'),
                'currentDraftVersion.uploader:id,name',
                'currentSupportingPhotoVersion.uploader:id,name',
                'versions' => fn ($version) => $version->with('uploader:id,name')->latest('version_number'),
                'uploadHistories' => fn ($history) => $history->with('actor:id,name')->latest('created_at'),
            ])->orderBy('id'),
        ]);

        return view('ib39.fea.show', compact('fea'));
    }
}
