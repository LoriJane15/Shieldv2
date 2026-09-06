<?php

namespace App\Http\Controllers\Mblrc;

use App\Http\Controllers\Controller;
use App\Models\FormerRebel;
use App\Models\FrProgramStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'registered' => FormerRebel::count(),
            // Legacy counted "enrolled" as former rebels whose status is Active.
            'active' => FormerRebel::where('status', 'Active')->count(),
            'reintegrated' => FormerRebel::where('status', 'Reintegrated')->count(),
            'completed' => FrProgramStatus::where('reintegration_status', 'Completed')->count(),
            'ongoing' => FrProgramStatus::where('reintegration_status', 'On-going')->count(),
            'not_started' => FrProgramStatus::where('reintegration_status', 'Not-Started')->count(),
        ];

        // "As of <date>" captions under each headline figure, as the legacy cards had.
        $fmt = fn ($d) => $d ? Carbon::parse($d)->format('M d, Y') : now()->format('M d, Y');

        $asOf = [
            'registered' => $fmt(FormerRebel::max('registered_at')),
            'enrolled' => $fmt(FormerRebel::where('status', 'Active')->max('updated_at')),
            'completed' => $fmt(FrProgramStatus::where('reintegration_status', 'Completed')->max('reintegration_date')),
            'reintegrated' => $fmt(FormerRebel::where('status', 'Reintegrated')->max('updated_at')),
        ];

        return view('mblrc.dashboard', compact('stats', 'asOf'));
    }

    /** Monthly time-series for the two dashboard line charts (last 7 months). */
    public function analytics(): JsonResponse
    {
        $months = collect(range(6, 0))->map(fn ($i) => Carbon::now()->startOfMonth()->subMonths($i));
        $labels = $months->map(fn ($m) => $m->format('M Y'));

        $program = ['not_started' => [], 'ongoing' => [], 'completed' => []];
        $overall = ['registered' => [], 'reintegrated' => []];

        foreach ($months as $m) {
            $end = (clone $m)->endOfMonth();

            $program['not_started'][] = FrProgramStatus::where('reintegration_status', 'Not-Started')
                ->where('reintegration_date', '<=', $end)->count();
            $program['ongoing'][] = FrProgramStatus::where('reintegration_status', 'On-going')
                ->where('reintegration_date', '<=', $end)->count();
            $program['completed'][] = FrProgramStatus::where('reintegration_status', 'Completed')
                ->where('reintegration_date', '<=', $end)->count();

            $overall['registered'][] = FormerRebel::where('registered_at', '<=', $end)->count();
            $overall['reintegrated'][] = FormerRebel::where('status', 'Reintegrated')
                ->whereNotNull('latitude')->where('updated_at', '<=', $end)->count();
        }

        return response()->json([
            'labels' => $labels,
            'program' => $program,
            'overall' => $overall,
        ]);
    }

    /** Distribution stats for pie/bar widgets. */
    public function statistics(): JsonResponse
    {
        return response()->json([
            'status' => FormerRebel::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')->pluck('count', 'status'),
            'gender' => FormerRebel::select('gender', DB::raw('COUNT(*) as count'))
                ->whereNotNull('gender')->groupBy('gender')->pluck('count', 'gender'),
            'batch' => FormerRebel::select('batch_year', DB::raw('COUNT(*) as count'))
                ->whereNotNull('batch_year')->groupBy('batch_year')->orderBy('batch_year')->pluck('count', 'batch_year'),
        ]);
    }
}
