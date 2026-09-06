<?php

namespace Tests\Feature;

use App\Models\{User, RcspBarangay, RcspForm, Implementation, FormerRebel};
use Tests\TestCase;

class SmokeDetailTest extends TestCase
{
    public function test_detail_pages_render(): void
    {
        $this->skipUnlessLegacyDataPresent();


        $bgy    = RcspBarangay::first()?->id;
        $form   = RcspForm::whereNotNull('file')->first()?->id;
        $implan = Implementation::first()?->id;
        $fr     = FormerRebel::first()?->id;
        $cluster= \App\Models\RcspPhase::first()?->id;

        $cases = [
            ['admin',      "/katuparan/rcsp/$bgy"],
            ['admin',      "/katuparan/rcsp-form/$form/file"],
            ['admin',      "/katuparan/implan/$implan"],
            ['lgu',        "/lgu/rcsp/$bgy/monitoring"],
            ['lgu',        "/lgu/rcsp-form/$form/file"],
            ['lgu',        "/lgu/implan/$implan"],
            ['gov_agency', "/agency/implan/$implan"],
            ['mblrc',      "/mblrc/former-rebels/$fr"],
            ['mblrc',      "/mblrc/former-rebels/$fr/edit"],
            ['mblrc',      "/mblrc/former-rebels/$fr/location-history"],
        ];

        $fail = [];
        foreach ($cases as [$role, $uri]) {
            $user = User::where('role', $role)->first();
            $code = $this->actingAs($user)->get($uri)->getStatusCode();
            $ok = in_array($code, [200, 302, 403, 404]);
            fwrite(STDERR, sprintf("  %s %-4s %-11s %s\n", $ok ? 'ok ' : 'FAIL', $code, $role, $uri));
            if (! $ok) $fail[] = "$role $uri -> $code";
        }
        fwrite(STDERR, "\n");
        $this->assertSame([], $fail, implode("\n", $fail));
    }
}
