<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Permission model for fine-grained access control. Defines specific permissions (e.g.,
 * can_export_inventory, can_delete_poles) that can be assigned to roles for granular
 * authorization beyond the basic RBAC.
 *
 * Data Flow:
 *   Admin defines permissions → Assign to roles → Middleware checks permission → Grant
 *   or deny access
 *
 * @depends-on Role
 * @business-domain Security
 * @package App\Models
 */
class Permission extends Model
{
    use HasFactory;
}
