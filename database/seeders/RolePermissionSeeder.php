<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;


class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            0 => 'admin',
            1 => 'site engineer',
            2 => 'project manager',
            3 => 'vendor',
            4 => 'store incharge',
            5 => 'hr manager',
            10 => 'client',
        ];

        foreach ($roles as $code => $name) {
            Role::firstOrCreate(['name' => $name]);
        }
    }
}
