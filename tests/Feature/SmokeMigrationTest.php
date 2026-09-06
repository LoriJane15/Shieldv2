<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SmokeMigrationTest extends TestCase
{
    /** Hit every GET route that needs no parameters, as a user of the matching role. */
    public function test_every_role_get_route_responds(): void
    {
        $this->skipUnlessLegacyDataPresent();


        $byRole = [];
        foreach (Route::getRoutes() as $r) {
            if (! in_array('GET', $r->methods()) || str_contains($r->uri(), '{')) continue;
            $mw = implode(',', $r->gatherMiddleware());
            if (! preg_match('/role:([a-z0-9_]+)/', $mw, $m)) continue;
            $byRole[$m[1]][] = $r->uri();
        }

        $fail = [];
        foreach ($byRole as $role => $uris) {
            $user = User::where('role', $role)->first();
            if (! $user) { $fail[] = "NO USER for role $role"; continue; }
            foreach ($uris as $uri) {
                $res = $this->actingAs($user)->get('/'.$uri);
                $code = $res->getStatusCode();
                $mark = in_array($code, [200, 302]) ? 'ok ' : 'FAIL';
                fwrite(STDERR, sprintf("  %s %-4s %-12s /%s\n", $mark, $code, $role, $uri));
                if ($mark === 'FAIL') $fail[] = "$role /$uri -> $code";
            }
        }
        fwrite(STDERR, "\n");
        $this->assertSame([], $fail, "Broken routes:\n".implode("\n", $fail));
    }
}
