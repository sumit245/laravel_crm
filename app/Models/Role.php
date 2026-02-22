<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Role model for role-based access control. Defines system roles (Admin, Project Manager, Site
 * Engineer, Vendor) with associated permissions. Each user belongs to one role.
 *
 * Data Flow:
 *   Admin defines role → Assign permissions → Assign to users → Middleware checks role
 *   for route access
 *
 * @depends-on Permission
 * @business-domain Security
 * @package App\Models
 */
class Role extends Model
{
    use HasFactory;
}
