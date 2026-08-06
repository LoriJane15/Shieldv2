<?php

namespace Tests\Feature;

use App\Models\AgencyImplanResponse;
use App\Models\FormerRebel;
use App\Models\Implementation;
use App\Models\MapBarangay;
use App\Models\RcspBarangay;
use App\Models\RcspPhase;
use App\Models\User;
use Database\Seeders\DevelopmentSeeder;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevelopmentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_scoped_accounts_and_synthetic_workflow_data(): void
    {
        config()->set('shield.development_seed_password', 'local-test-password');

        $this->seed(LocationSeeder::class);
        $this->seed(DevelopmentSeeder::class);

        $this->assertSame(
            [
                '39th_ib', 'admin', 'afp', 'dilg_fms', 'dilg_provincial_focal',
                'dilg_regional', 'gov_agency', 'japic', 'lgu', 'local_eclip_committee',
                'lswdo', 'mblrc', 'nboo_eclip_pmo', 'pnp', 'super_admin',
            ],
            User::query()->orderBy('role')->pluck('role')->all()
        );
        $this->assertNotNull(User::query()->where('role', 'lgu')->firstOrFail()->municipality_id);
        $this->assertNotNull(User::query()->where('role', 'gov_agency')->firstOrFail()->gov_agency_id);
        foreach (['lswdo', 'dilg_provincial_focal', 'local_eclip_committee'] as $role) {
            $this->assertNotNull(User::query()->where('role', $role)->firstOrFail()->municipality_id);
        }
        $this->assertNull(User::query()->where('role', 'japic')->firstOrFail()->municipality_id);

        $this->assertSame(6, RcspPhase::query()->count());
        $this->assertSame(3, RcspBarangay::query()->count());
        $this->assertSame(4, Implementation::query()->count());
        $this->assertGreaterThan(0, AgencyImplanResponse::query()->count());
        $this->assertSame(3, FormerRebel::query()->count());
        $this->assertSame(3, MapBarangay::query()->count());

        $this->assertSame(
            3,
            FormerRebel::query()->where('firstname', 'Synthetic')->count()
        );
    }

    public function test_it_can_be_run_repeatedly_without_duplicating_seed_records(): void
    {
        config()->set('shield.development_seed_password', 'local-test-password');

        $this->seed(LocationSeeder::class);
        $this->seed(DevelopmentSeeder::class);
        $this->seed(DevelopmentSeeder::class);

        $this->assertSame(15, User::query()->count());
        $this->assertSame(6, RcspPhase::query()->count());
        $this->assertSame(3, RcspBarangay::query()->count());
        $this->assertSame(4, Implementation::query()->count());
        $this->assertSame(3, FormerRebel::query()->count());
        $this->assertSame(3, MapBarangay::query()->count());
    }
}
