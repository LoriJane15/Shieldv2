<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormerRebel;
use App\Models\Implementation;
use App\Models\Municipality;
use App\Models\RcspBarangay;
use App\Models\User;
use Illuminate\View\View;

class OverviewController extends Controller
{
    /** Bulk File Submission — IMPLAN, grouped by status (Ongoing / Awaiting / Verified). */
    public function agencies(): View
    {
        $all = Implementation::latest()->get();

        $tabs = [
            'ongoing' => $all->where('status', 'ongoing'),
            'awaiting' => $all->where('status', 'not yet started'),
            'verified' => $all->where('status', 'verified'),
        ];

        // Lookup: rcsp_barangay id => "Barangay - Municipality" for the Target Area column.
        $areaNames = RcspBarangay::with(['barangay', 'municipality'])->get()
            ->mapWithKeys(fn ($r) => [
                $r->id => ($r->barangay?->name ?? "Barangay #{$r->barangay_id}").' - '.($r->municipality?->name ?? ''),
            ]);

        return view('admin.overview.agencies', compact('tabs', 'areaNames'));
    }

    /** RCSP barangays grouped by municipality. */
    public function locations(): View
    {
        $municipalities = Municipality::orderBy('name')->get()->map(fn ($m) => [
            'name' => trim($m->name),
            'barangays' => RcspBarangay::with('barangay')->where('municipality_id', $m->id)->get(),
        ])->filter(fn ($r) => $r['barangays']->isNotEmpty());

        $frPoints = FormerRebel::whereNotNull('latitude')->whereNotNull('longitude')
            ->get(['firstname', 'lastname', 'placement_address', 'latitude', 'longitude', 'status'])
            ->map(fn ($fr) => [
                'name' => trim("{$fr->firstname} {$fr->lastname}"),
                'address' => $fr->placement_address,
                'lat' => (float) $fr->latitude,
                'lng' => (float) $fr->longitude,
                'status' => $fr->status,
            ]);

        return view('admin.overview.locations', compact('municipalities', 'frPoints'));
    }

    /** SHIELD 12-cluster reference with overall IMPLAN status rollup. */
    public function clusters(): View
    {
        $clusters = [
            'Basic Services', 'Comprehensive', 'Cooperation', 'Empowerment',
            'Enforcement', 'Infrastructure', 'International', 'Livelihood',
            'Local Peace', 'Sectoral', 'Situational', 'Strategic',
        ];

        $rollup = [
            'total' => Implementation::count(),
            'verified' => Implementation::where('status', 'verified')->count(),
            'ongoing' => Implementation::where('status', 'ongoing')->count(),
            'for_verification' => Implementation::where('status', 'for verification')->count(),
        ];

        return view('admin.overview.clusters', compact('clusters', 'rollup'));
    }

    /** Profile page for a single SHIELD cluster (agencies + members). */
    public function clusterProfile(string $slug): View
    {
        $clusters = config('shield_clusters');
        abort_unless(isset($clusters[$slug]), 404);

        return view('admin.overview.cluster-profile', [
            'cluster' => $clusters[$slug],
            'slug' => $slug,
            'allClusters' => $clusters,
        ]);
    }

    /** Read-only directory of all system users. */
    public function users(): View
    {
        $users = User::with(['municipality', 'govAgency'])
            ->orderBy('role')->orderBy('name')->get()->groupBy('role');

        return view('admin.overview.users', compact('users'));
    }
}
