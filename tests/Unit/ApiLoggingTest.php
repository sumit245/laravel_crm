<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\API\SiteController;
use App\Http\Controllers\API\StaffController;
use App\Http\Controllers\API\InventoryController;

class ApiLoggingTest extends TestCase
{
    public function test_api_site_controller_resolves_activity_logger()
    {
        $controller = app(SiteController::class);
        $this->assertNotNull($controller);
    }
    
    public function test_api_staff_controller_resolves_activity_logger()
    {
        $controller = app(StaffController::class);
        $this->assertNotNull($controller);
    }
    
    public function test_api_inventory_controller_resolves_activity_logger()
    {
        $controller = app(InventoryController::class);
        $this->assertNotNull($controller);
    }
}
