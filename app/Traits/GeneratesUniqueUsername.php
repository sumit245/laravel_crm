<?php

namespace App\Traits;

use App\Models\User;

trait GeneratesUniqueUsername
{
    /**
     * Generate a unique username based on the user's name.
     *
     * @param string $name
     * @return string
     */
    protected function generateUniqueUsername(string $name): string
    {
        $baseUsername = strtolower(preg_replace('/\s+/', '', $name));
        $randomSuffix = mt_rand(1000, 9999);
        $username = $baseUsername . $randomSuffix;

        // Ensure the username is unique
        while (User::where('username', $username)->exists()) {
            $randomSuffix = mt_rand(1000, 9999);
            $username = $baseUsername . $randomSuffix;
        }

        return $username;
    }
}
