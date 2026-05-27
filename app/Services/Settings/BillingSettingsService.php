<?php

namespace App\Services\Settings;

use App\Models\City;
use App\Models\User;
use App\Models\UserCategory;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class BillingSettingsService
{
    public function __construct(private SettingsAuditService $audit)
    {
    }

    public function data(): array
    {
        return [
            'vehicles' => Vehicle::orderBy('vehicle_name')->get(),
            'users' => User::with('usercategory')->where('role', '!=', \App\Enums\UserRole::VENDOR->value)->orderBy('firstName')->get(),
            'categories' => UserCategory::orderBy('category_code')->get(),
            'cities' => City::orderBy('name')->get(),
        ];
    }

    public function createVehicle(array $data, ?int $userId): Vehicle
    {
        $vehicle = Vehicle::create($data);
        $this->audit->log('billing.vehicle', (string) $vehicle->id, [], $vehicle->toArray(), $userId);

        return $vehicle;
    }

    public function updateVehicle(Vehicle $vehicle, array $data, ?int $userId): Vehicle
    {
        $before = $vehicle->toArray();
        $vehicle->update($data);
        $this->audit->log('billing.vehicle', (string) $vehicle->id, $before, $vehicle->fresh()->toArray(), $userId);

        return $vehicle;
    }

    public function deleteVehicle(Vehicle $vehicle, ?int $userId): void
    {
        $before = $vehicle->toArray();
        DB::transaction(function () use ($vehicle) {
            $vehicleId = $vehicle->id;
            $vehicle->delete();

            UserCategory::query()->each(function (UserCategory $category) use ($vehicleId) {
                $allowed = $this->decodeAllowedVehicles($category->allowed_vehicles);
                $updated = array_values(array_filter($allowed, fn ($id) => (int) $id !== (int) $vehicleId));
                if ($allowed !== $updated) {
                    $category->allowed_vehicles = json_encode($updated);
                    $category->save();
                }
            });
        });

        $this->audit->log('billing.vehicle', (string) $vehicle->id, $before, [], $userId);
    }

    public function createCategory(array $data, ?int $userId): UserCategory
    {
        $category = UserCategory::create([
            'category_code' => $data['category_code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'allowed_vehicles' => json_encode($data['vehicle_ids']),
            'city_category' => $data['city_category'],
            'dailyamount' => $data['daily_amount'],
        ]);

        $this->audit->log('billing.category', (string) $category->id, [], $category->toArray(), $userId);

        return $category;
    }

    public function updateCategory(UserCategory $category, array $data, ?int $userId): UserCategory
    {
        $before = $category->toArray();
        $category->fill([
            'category_code' => $data['category_code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'allowed_vehicles' => json_encode($data['vehicle_ids']),
            'city_category' => $data['city_category'],
            'dailyamount' => $data['daily_amount'],
        ]);
        $category->save();

        $this->audit->log('billing.category', (string) $category->id, $before, $category->fresh()->toArray(), $userId);

        return $category;
    }

    public function deleteCategory(UserCategory $category, ?int $userId): void
    {
        $before = $category->toArray();
        $category->delete();
        $this->audit->log('billing.category', (string) $category->id, $before, [], $userId);
    }

    public function updateUserCategory(User $user, int $categoryId, ?int $actorId): User
    {
        $before = ['category' => $user->category];
        $user->category = $categoryId;
        $user->save();
        $this->audit->log('billing.user_category', (string) $user->id, $before, ['category' => $user->category], $actorId);

        return $user;
    }

    public function updateCity(City $city, array $data, ?int $userId): City
    {
        $before = $city->toArray();
        $city->fill([
            'name' => $data['city_name'],
            'category' => $data['city_category'],
        ]);
        $city->save();
        $this->audit->log('billing.city', (string) $city->id, $before, $city->fresh()->toArray(), $userId);

        return $city;
    }

    public function decodeAllowedVehicles(mixed $value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? array_values($decoded) : [];
        }

        return [];
    }
}
