<?php

namespace Tests\Unit;

use App\Helpers\DateFormatter;
use PHPUnit\Framework\TestCase;

class DateFormatterTest extends TestCase
{
    /**
     * Test that null input returns null output
     * Validates: Requirements 3.3
     */
    public function test_null_input_returns_null(): void
    {
        $result = DateFormatter::formatToDDMMYYYY(null);
        $this->assertNull($result);
    }

    /**
     * Test basic date formatting with leading zeros
     * Validates: Requirements 3.1, 3.4, 3.5
     */
    public function test_formats_date_with_leading_zeros(): void
    {
        $result = DateFormatter::formatToDDMMYYYY('2025-01-05 10:30:45');
        $this->assertEquals('05/01/2025', $result);
    }

    /**
     * Test date formatting without leading zeros needed
     * Validates: Requirements 3.1, 3.5
     */
    public function test_formats_date_without_leading_zeros_needed(): void
    {
        $result = DateFormatter::formatToDDMMYYYY('2025-12-25 15:45:30');
        $this->assertEquals('25/12/2025', $result);
    }

    /**
     * Test date formatting with single digit day and month
     * Validates: Requirements 3.4
     */
    public function test_formats_single_digit_day_and_month_with_leading_zeros(): void
    {
        $result = DateFormatter::formatToDDMMYYYY('2025-03-05 00:00:00');
        $this->assertEquals('05/03/2025', $result);
    }

    /**
     * Test date formatting with date-only format (no time)
     * Validates: Requirements 3.1, 3.5
     */
    public function test_formats_date_only_format(): void
    {
        $result = DateFormatter::formatToDDMMYYYY('2025-06-15');
        $this->assertEquals('15/06/2025', $result);
    }

    /**
     * Test date formatting with different year
     * Validates: Requirements 3.5
     */
    public function test_formats_date_with_different_year(): void
    {
        $result = DateFormatter::formatToDDMMYYYY('2024-11-20 08:15:22');
        $this->assertEquals('20/11/2024', $result);
    }

    /**
     * Test date formatting for first day of year
     * Validates: Requirements 3.4, 3.5
     */
    public function test_formats_first_day_of_year(): void
    {
        $result = DateFormatter::formatToDDMMYYYY('2025-01-01 00:00:00');
        $this->assertEquals('01/01/2025', $result);
    }

    /**
     * Test date formatting for last day of year
     * Validates: Requirements 3.5
     */
    public function test_formats_last_day_of_year(): void
    {
        $result = DateFormatter::formatToDDMMYYYY('2025-12-31 23:59:59');
        $this->assertEquals('31/12/2025', $result);
    }

    /**
     * Test date formatting for leap year date
     * Validates: Requirements 3.1, 3.5
     */
    public function test_formats_leap_year_date(): void
    {
        $result = DateFormatter::formatToDDMMYYYY('2024-02-29 12:00:00');
        $this->assertEquals('29/02/2024', $result);
    }

    /**
     * Test date formatting for non-leap year February 28th
     * Validates: Requirements 3.1, 3.5
     */
    public function test_formats_non_leap_year_february_28(): void
    {
        $result = DateFormatter::formatToDDMMYYYY('2025-02-28 12:00:00');
        $this->assertEquals('28/02/2025', $result);
    }

    /**
     * Test date formatting for end of month with 30 days
     * Validates: Requirements 3.1, 3.5
     */
    public function test_formats_end_of_30_day_month(): void
    {
        $result = DateFormatter::formatToDDMMYYYY('2025-04-30 23:59:59');
        $this->assertEquals('30/04/2025', $result);
    }

    /**
     * Test date formatting for end of month with 31 days
     * Validates: Requirements 3.1, 3.5
     */
    public function test_formats_end_of_31_day_month(): void
    {
        $result = DateFormatter::formatToDDMMYYYY('2025-01-31 23:59:59');
        $this->assertEquals('31/01/2025', $result);
    }

    /**
     * Test date formatting for century boundary year
     * Validates: Requirements 3.5
     */
    public function test_formats_century_boundary_year(): void
    {
        $result = DateFormatter::formatToDDMMYYYY('2000-01-01 00:00:00');
        $this->assertEquals('01/01/2000', $result);
    }

    /**
     * Test date formatting for year 2100 (non-leap century year)
     * Validates: Requirements 3.5
     */
    public function test_formats_non_leap_century_year(): void
    {
        $result = DateFormatter::formatToDDMMYYYY('2100-02-28 12:00:00');
        $this->assertEquals('28/02/2100', $result);
    }
}
