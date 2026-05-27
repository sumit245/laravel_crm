<?php

namespace Tests\Unit;

use App\Services\Settings\BillingSettingsService;
use App\Services\Settings\SettingsAuditService;
use Tests\TestCase;

class BillingSettingsServiceTest extends TestCase
{
    public function test_decode_allowed_vehicles_handles_array_json_and_invalid(): void
    {
        $service = new BillingSettingsService(new SettingsAuditService());

        $this->assertSame([1, 2], $service->decodeAllowedVehicles([1, 2]));
        $this->assertSame([3, 4], $service->decodeAllowedVehicles('[3,4]'));
        $this->assertSame([], $service->decodeAllowedVehicles('not-json'));
        $this->assertSame([], $service->decodeAllowedVehicles(null));
    }
}
