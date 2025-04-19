<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        $roleMap = [
            0 => 'admin',
            1 => 'site engineer',
            2 => 'project manager',
            3 => 'vendor',
            4 => 'store incharge',
            5 => 'hr manager',
            10 => 'client',
        ];

        $users = User::all();

        foreach ($users as $user) {
            $roleName = $roleMap[$user->role] ?? null;

            if ($roleName) {
                $user->syncRoles([$roleName]); // replaces existing roles
            }
        }
    }
}
