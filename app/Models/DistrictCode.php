<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * District code master data for geographic hierarchy. Maps district names to standardized codes
 * used in pole numbering format (e.g., first 3 letters of district name).
 *
 * Data Flow:
 *   Loaded during pole number generation → Provides standardized district prefix → Used in
 *   complete_pole_number format
 *
 * @business-domain Core Domain
 * @package App\Models
 */
class DistrictCode extends Model
{
    use HasFactory;
}
