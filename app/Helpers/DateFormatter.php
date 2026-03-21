<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Class DateFormatter
 *
 * Helper class for date formatting operations
 *
 * @package App\Helpers
 */
class DateFormatter
{
    /**
     * Convert ISO timestamp format to dd/mm/yyyy format
     *
     * @param string|null $isoDate ISO format timestamp (e.g., "2025-01-15 10:30:45")
     * @return string|null Formatted date string (e.g., "15/01/2025") or null if input is null
     */
    public static function formatToDDMMYYYY(?string $isoDate): ?string
    {
        // If input is null, return null
        if ($isoDate === null) {
            return null;
        }

        // Parse the ISO date string using Carbon and format as dd/mm/yyyy with leading zeros
        return Carbon::parse($isoDate)->format('d/m/Y');
    }
}
