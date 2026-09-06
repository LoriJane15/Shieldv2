<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class MblrcDashboardTest extends TestCase
{
    public function test_dashboard_matches_the_legacy_layout(): void
    {
        $this->skipUnlessLegacyDataPresent();

        $user = User::where('role', 'mblrc')->firstOrFail();
        $html = $this->actingAs($user)->get('/mblrc')->assertOk()->getContent();

        // Welcome bar + date dropdown
        $this->assertStringContainsString('Welcome Mindanao Baptist Rural Learning Center', $html);
        $this->assertStringContainsString('dropdownMenuDate2', $html);

        // The four headline cards, with the legacy labels and "As of" captions
        foreach ([
            'Total Registered Former Rebels',
            'Total Enrolled in Program',
            'Total Completed 3-Month Program',
            'Total Former Rebels Reintegrated',
            'Not-Started Program',
            'On-going Program',
            'Quick Actions',
        ] as $label) {
            $this->assertStringContainsString($label, $html, "missing card: $label");
        }

        $this->assertStringContainsString('As of', $html);
        $this->assertStringContainsString('mblrc-dashboard.css', $html);

        // Charts + map
        foreach (['Program Status Analytics', 'Overall Statistics',
                  'Former Rebels Location Map', 'programChart', 'overallChart', 'frMap'] as $needle) {
            $this->assertStringContainsString($needle, $html, "missing: $needle");
        }
    }

    /** The bundle already draws these; a second inline copy double-initialises Chart.js. */
    public function test_dashboard_has_no_duplicate_inline_chart_script(): void
    {
        $this->skipUnlessLegacyDataPresent();

        $user = User::where('role', 'mblrc')->firstOrFail();
        $html = $this->actingAs($user)->get('/mblrc')->assertOk()->getContent();

        $this->assertStringNotContainsString('cdn.jsdelivr.net/npm/chart.js', $html);
        $this->assertStringNotContainsString('unpkg.com/leaflet', $html);
        $this->assertStringNotContainsString('new Chart(', $html);
    }

    public function test_analytics_endpoint_feeds_both_charts(): void
    {
        $this->skipUnlessLegacyDataPresent();

        $user = User::where('role', 'mblrc')->firstOrFail();
        $data = $this->actingAs($user)->get('/mblrc/analytics')->assertOk()->json();

        $this->assertCount(7, $data['labels'], 'seven months of trend data');
        foreach (['not_started', 'ongoing', 'completed'] as $k) {
            $this->assertCount(7, $data['program'][$k]);
        }
        $this->assertCount(7, $data['overall']['registered']);
    }

    /**
     * mblrc.js gates initDashboard() on this element id. When the view and the
     * script disagreed, the charts and map silently never drew.
     */
    public function test_dashboard_data_element_matches_the_script(): void
    {
        $this->skipUnlessLegacyDataPresent();

        $user = User::where('role', 'mblrc')->firstOrFail();
        $html = $this->actingAs($user)->get('/mblrc')->assertOk()->getContent();

        $this->assertStringContainsString('id="mblrcData"', $html);
        $this->assertStringContainsString('data-analytics=', $html);
    }
}
