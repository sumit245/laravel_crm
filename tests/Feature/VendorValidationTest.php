<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VendorValidationTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('username')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->integer('role')->default(1);
            $table->string('status')->nullable()->default('active');
            $table->boolean('disableLogin')->default(false);
            $table->string('image')->nullable();
            $table->string('address')->nullable();
            $table->string('contactNo')->nullable();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('module', 64);
            $table->string('action', 64);
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('description', 255)->nullable();
            $table->json('changes')->nullable();
            $table->json('extra')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('request_id', 100)->nullable();
            $table->string('batch_id', 100)->nullable();
            $table->timestamps();
        });

        $this->admin = User::create([
            'name'        => 'Admin',
            'firstName'   => 'Admin',
            'lastName'    => 'User',
            'username'    => 'admin_user',
            'email'       => 'admin@example.com',
            'password'    => Hash::make('password'),
            'role'        => UserRole::ADMIN->value,
            'status'      => 'active',
            'disableLogin'=> false,
        ]);
    }

    private function actingAsAdmin()
    {
        return $this->actingAs($this->admin, 'sanctum');
    }

    // ─── Create validation ────────────────────────────────────────────────────

    public function test_create_vendor_requires_email(): void
    {
        $response = $this->actingAsAdmin()->postJson('/api/vendor', [
            'name'                  => 'Test Vendor',
            'firstName'             => 'Test',
            'lastName'              => 'Vendor',
            'username'              => 'testvendor',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_create_vendor_rejects_invalid_email_format(): void
    {
        $response = $this->actingAsAdmin()->postJson('/api/vendor', [
            'name'                  => 'Test Vendor',
            'firstName'             => 'Test',
            'lastName'              => 'Vendor',
            'email'                 => 'not-an-email',
            'username'              => 'testvendor2',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_create_vendor_rejects_duplicate_email(): void
    {
        User::create([
            'name'        => 'Existing',
            'firstName'   => 'Existing',
            'lastName'    => 'Vendor',
            'username'    => 'existing_vendor',
            'email'       => 'vendor@example.com',
            'password'    => Hash::make('password'),
            'role'        => UserRole::VENDOR->value,
            'status'      => 'active',
        ]);

        $response = $this->actingAsAdmin()->postJson('/api/vendor', [
            'name'                  => 'Another Vendor',
            'firstName'             => 'Another',
            'lastName'              => 'Vendor',
            'email'                 => 'vendor@example.com',
            'username'              => 'another_vendor',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_create_vendor_requires_password_confirmation(): void
    {
        $response = $this->actingAsAdmin()->postJson('/api/vendor', [
            'name'      => 'Test Vendor',
            'firstName' => 'Test',
            'lastName'  => 'Vendor',
            'email'     => 'newvendor@example.com',
            'username'  => 'newvendor',
            'password'  => 'password123',
            // missing password_confirmation
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_create_vendor_success(): void
    {
        $response = $this->actingAsAdmin()->postJson('/api/vendor', [
            'name'                  => 'New Vendor',
            'firstName'             => 'New',
            'lastName'              => 'Vendor',
            'email'                 => 'newvendor@example.com',
            'username'              => 'newvendor123',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'newvendor@example.com',
            'role'  => UserRole::VENDOR->value,
        ]);
    }

    // ─── Show returns 404 for missing vendor ─────────────────────────────────

    public function test_show_returns_404_for_nonexistent_vendor(): void
    {
        $response = $this->actingAsAdmin()->getJson('/api/vendor/99999');

        $response->assertStatus(404);
    }

    // ─── Update email uniqueness ──────────────────────────────────────────────

    public function test_update_vendor_rejects_duplicate_email(): void
    {
        $vendor1 = User::create([
            'name'        => 'Vendor One',
            'firstName'   => 'Vendor',
            'lastName'    => 'One',
            'username'    => 'vendor_one',
            'email'       => 'vendorone@example.com',
            'password'    => Hash::make('password'),
            'role'        => UserRole::VENDOR->value,
            'status'      => 'active',
        ]);

        User::create([
            'name'        => 'Vendor Two',
            'firstName'   => 'Vendor',
            'lastName'    => 'Two',
            'username'    => 'vendor_two',
            'email'       => 'vendortwo@example.com',
            'password'    => Hash::make('password'),
            'role'        => UserRole::VENDOR->value,
            'status'      => 'active',
        ]);

        $response = $this->actingAsAdmin()->putJson("/api/vendor/{$vendor1->id}", [
            'email' => 'vendortwo@example.com', // already taken
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }
}
