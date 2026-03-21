<?php

namespace Tests\Unit;

use App\Helpers\DateFormatter;
use Eris\Generator;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Property-based tests for DateFormatter
 * 
 * Feature: vendor-sites-api-modification
 */
class DateFormatterPropertyTest extends TestCase
{
    use TestTrait;

    /**
     * Property 4: Date Format Validation
     * 
     * **Validates: Requirements 3.1, 3.2, 3.4, 3.5**
     * 
     * For any non-null created_at or updated_at timestamp in the database,
     * the formatted date in the response should match the pattern dd/mm/yyyy
     * where day and month are two digits with leading zeros (e.g., "05/03/2025")
     * and year is four digits.
     */
    public function test_property_date_format_validation(): void
    {
        $this->minimumEvaluationRatio(0.5)
            ->withRand(function() { return new \Eris\Random\RandomRange(1, 20); });
        
        $this->forAll(
            Generator\choose(1, 28),      // day (1-28 to avoid month-specific issues)
            Generator\choose(1, 12),      // month (1-12)
            Generator\choose(2000, 2099), // year (reasonable range)
            Generator\choose(0, 23),      // hour
            Generator\choose(0, 59),      // minute
            Generator\choose(0, 59)       // second
        )
        ->then(function ($day, $month, $year, $hour, $minute, $second) {
            // Create ISO format date string
            $isoDate = sprintf(
                '%04d-%02d-%02d %02d:%02d:%02d',
                $year,
                $month,
                $day,
                $hour,
                $minute,
                $second
            );

            // Format the date
            $result = DateFormatter::formatToDDMMYYYY($isoDate);

            // Assert: Result matches dd/mm/yyyy pattern
            $this->assertMatchesRegularExpression(
                '/^\d{2}\/\d{2}\/\d{4}$/',
                $result,
                "Date format should match dd/mm/yyyy pattern"
            );

            // Assert: Day is formatted with leading zero
            $expectedDay = sprintf('%02d', $day);
            $this->assertStringStartsWith(
                $expectedDay,
                $result,
                "Day should be formatted with leading zero"
            );

            // Assert: Month is formatted with leading zero
            $expectedMonth = sprintf('%02d', $month);
            $parts = explode('/', $result);
            $this->assertEquals(
                $expectedMonth,
                $parts[1],
                "Month should be formatted with leading zero"
            );

            // Assert: Year is four digits
            $this->assertEquals(
                sprintf('%04d', $year),
                $parts[2],
                "Year should be four digits"
            );

            // Assert: The formatted date represents the correct date
            $expectedFormat = sprintf('%02d/%02d/%04d', $day, $month, $year);
            $this->assertEquals(
                $expectedFormat,
                $result,
                "Formatted date should match expected dd/mm/yyyy format"
            );
        });
    }
}
