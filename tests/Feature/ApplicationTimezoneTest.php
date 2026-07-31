<?php

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Tests\TestCase;

class ApplicationTimezoneTest extends TestCase
{
    public function test_application_stores_utc_and_displays_philippine_standard_time(): void
    {
        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame('UTC', date_default_timezone_get());
        $this->assertSame('Asia/Manila', config('app.display_timezone'));
        $this->assertSame('+08:00', now(config('app.display_timezone'))->format('P'));
        $this->assertSame(
            'July 31, 2026 9:52 AM',
            Carbon::parse('2026-07-31 01:52:00', 'UTC')
                ->timezone(config('app.display_timezone'))
                ->format('F j, Y g:i A'),
        );
    }
}
