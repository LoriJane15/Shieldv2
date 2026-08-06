<?php

namespace App\Http\Controllers;

use App\Models\EclipReportExport;
use App\Services\EclipAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EclipAnalyticsController extends Controller
{
    public function index(Request $request, EclipAnalyticsService $analytics): View
    {
        abort_unless($request->user()->canViewEclipAnalytics(), 403);

        return view('eclip_analytics.index', $analytics->dashboard($request->user()));
    }

    public function export(Request $request, EclipAnalyticsService $analytics): StreamedResponse
    {
        abort_unless($request->user()->canViewEclipAnalytics(), 403);
        $data = $analytics->dashboard($request->user());
        EclipReportExport::query()->create([
            'user_id' => $request->user()->id,
            'municipality_id' => $request->user()->hasRole('admin', 'super_admin') ? null : $request->user()->municipality_id,
            'format' => 'csv',
            'financial_included' => $data['financial'] !== null,
            'ip_address' => $request->ip(),
            'exported_at' => now(),
        ]);

        return response()->streamDownload(function () use ($data) {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['SHIELD 2.0 E-CLIP Aggregate Report']);
            fputcsv($output, ['Scope', $this->csvSafe($data['scopeLabel'])]);
            fputcsv($output, ['Delay threshold (days)', $data['delayDays']]);
            fputcsv($output, []);
            fputcsv($output, ['Summary', 'Count']);
            foreach ($data['summary'] as $label => $count) {
                fputcsv($output, [str($label)->replace('_', ' ')->title(), $count]);
            }
            fputcsv($output, []);
            fputcsv($output, ['Case status', 'Count']);
            foreach ($data['statusCounts'] as $label => $count) {
                fputcsv($output, [$label, $count]);
            }
            fputcsv($output, []);
            fputcsv($output, ['Workflow stage', 'Average hours']);
            foreach ($data['stageHours'] as $label => $hours) {
                fputcsv($output, [$label, $hours]);
            }
            fputcsv($output, []);
            fputcsv($output, ['Required documents certified', $data['documentCompleteness']['certified']]);
            fputcsv($output, ['Required documents expected', $data['documentCompleteness']['expected']]);
            fputcsv($output, ['Document completeness percentage', $data['documentCompleteness']['percentage']]);
            fputcsv($output, []);
            fputcsv($output, ['Completed month', 'Cases']);
            foreach ($data['completedTrend'] as $month => $count) {
                fputcsv($output, [$month, $count]);
            }

            if ($data['financial'] !== null) {
                fputcsv($output, []);
                fputcsv($output, ['Financial measure', 'Aggregate amount']);
                foreach ($data['financial'] as $label => $amount) {
                    fputcsv($output, [str($label)->title(), number_format($amount, 2, '.', '')]);
                }
            }
            if ($data['municipalities']) {
                fputcsv($output, []);
                fputcsv($output, ['Municipality', 'Total', 'Completed', 'Delayed']);
                foreach ($data['municipalities'] as $municipality) {
                    fputcsv($output, [$this->csvSafe($municipality['name']), $municipality['total'], $municipality['completed'], $municipality['delayed']]);
                }
            }
            fclose($output);
        }, 'eclip-aggregate-report-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
        ]);
    }

    private function csvSafe(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) ? "'{$value}" : $value;
    }
}
