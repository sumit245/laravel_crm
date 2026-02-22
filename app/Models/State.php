<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * State/province master data for geographic hierarchy. Used in site addresses, city
 * categorization, and dropdown population in forms. India-specific state list.
 *
 * Data Flow:
 *   Pre-populated master data → Used in dropdowns → Links to City → Used in
 *   site/panchayat addresses
 *
 * @business-domain Core Domain
 * @package App\Models
 */
class State extends Model
{
    use HasFactory;

     /**
     * Write code on Method
     *
     * @return response()
     */

     protected $fillable = [
        'name', 'country_id'
    ];
}
