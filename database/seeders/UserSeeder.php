<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin User',
                'email' => 'admin@dashandots.tech',
                'username' => 'admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'), // Use bcrypt for hashing
                'role' => 0, // Admin role
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
                'firstName' => 'Sumit',
                'lastName' => 'Ranjan',
                'image' => 'https://randomuser.me/api/portraits/men/1.jpg',
                'status' => 'active',
                'disableLogin' => 0,
                'address' => '123 gali, jhajjar, Haryana',
                'contactNo' => '9909230912',
                'lastOnline' => now(),
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@dashandots.tech',
                'username' => 'staffuser',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 1, // Staff role
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
                'firstName' => 'Bittu',
                'lastName' => 'Pratihas',
                'image' => 'https://randomuser.me/api/portraits/men/1.jpg',
                'status' => 'active',
                'disableLogin' => 0,
                'address' => '123 gali, jhajjar, Haryana',
                'contactNo' => '9909230911',
                'lastOnline' => now(),
            ],
            [
                'name' => 'Project Manager',
                'email' => 'pm@dashandots.tech',
                'username' => 'projmanager',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 2, // Project Manager role
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
                'firstName' => 'Rohit',
                'lastName' => 'Gupta',
                'image' => 'https://randomuser.me/api/portraits/men/1.jpg',
                'status' => 'active',
                'disableLogin' => 0,
                'address' => '123 gali, jhajjar, Haryana',
                'contactNo' => '9909230913',
                'lastOnline' => now(),
            ],
            [
                'name' => 'Vendor User',
                'email' => 'vendor@example.com',
                'username' => 'vendoruser',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 3, // Vendor role
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
                'firstName' => 'Rohit',
                'lastName' => 'Gupta',
                'image' => 'https://randomuser.me/api/portraits/men/1.jpg',
                'status' => 'active',
                'disableLogin' => 0,
                'address' => '123 gali, jhajjar, Haryana',
                'contactNo' => '9909230912',
                'lastOnline' => now(),
            ],

        ]);
    }
}
