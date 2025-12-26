# Database Relationship Observations
## Streetlight Inventory Flow Analysis

**Date**: 2025-01-XX  
**Scope**: Analysis of relationships between `streetlights`, `streetlight_tasks`, `streelight_poles`, `inventory_dispatch`, `inventory_streetlight`, and `users` tables

---

## Table Relationship Chain

```
streetlights (Site)
    ↓ (hasMany via site_id)
streetlight_tasks (Task)
    ↓ (hasMany via task_id)
streelight_poles (Pole)
    ↓ (consumes via streetlight_pole_id)
inventory_dispatch (Dispatch Record)
    ↑ (gets data from serial_number)
inventory_streetlight (Inventory Stock)
    ↑ (dispatched to)
users (role=3 VENDOR)
```

---

## 1. CRITICAL ISSUES & INCONSISTENCIES

### 1.1 Table Name Typo
**Issue**: Table name `streelight_poles` contains a typo (should be `streetlight_poles`)

**Evidence**:
- Model file: `app/Models/Pole.php` line 11: `protected $table = 'streelight_poles';`
- Migration: `2025_12_17_225907_add_vendor_id_to_streelight_poles_table.php` uses `streelight_poles`
- Consistent typo throughout codebase

**Impact**: 
- Code readability issues
- Potential confusion for new developers
- Refactoring will require extensive changes

**Recommendation**: Consider renaming table in future refactor, but current implementation is consistent.

---

### 1.2 Missing Reverse Relationship: Pole → InventoryDispatch
**Issue**: `Pole` model lacks `hasMany` relationship to `InventoryDispatch`

**Evidence**:
- `InventoryDispatch` has: `streetlightPole()` → `belongsTo(Pole::class, 'streetlight_pole_id')`
- `Pole` model does NOT have corresponding `inventoryDispatches()` or `dispatches()` method

**Impact**:
- Cannot easily query all dispatches for a pole using Eloquent
- Current code uses manual queries: `InventoryDispatch::where('streetlight_pole_id', $pole->id)`
- Missing relationship makes code less maintainable

**Recommendation**: Add to `Pole` model:
```php
public function inventoryDispatches()
{
    return $this->hasMany(InventoryDispatch::class, 'streetlight_pole_id');
}
```

---

### 1.3 Broken/Incorrect Relationship: InventoryDispatch → InventoryStreetLight
**Issue**: `InventoryDispatch::inventoryStreetLight()` uses `inventory_id` foreign key, but this column is NOT in the fillable array and relationship appears to be via `serial_number` instead.

**Evidence**:
- Model defines: `belongsTo(InventroyStreetLightModel::class, 'inventory_id')`
- Fillable array does NOT include `inventory_id`
- Actual relationship in code uses `serial_number` for lookups
- `InventroyStreetLightModel::dispatch()` uses `serial_number` as foreign key

**Impact**:
- `inventoryStreetLight()` relationship method may not work correctly
- Code uses manual lookups via `serial_number` instead of relationship
- Inconsistent relationship definitions

**Recommendation**: 
- Verify if `inventory_id` column exists in `inventory_dispatch` table
- If not, update relationship to use `serial_number`:
```php
// In InventoryDispatch model
public function inventoryStreetLight()
{
    return $this->belongsTo(InventroyStreetLightModel::class, 'serial_number', 'serial_number');
}
```

---

### 1.4 Inconsistent Relationship Definition: Streetlight → StreetlightTask
**Issue**: `Streetlight` model has confusing/multiple task relationship methods

**Problems**:
1. `tasks()` and `streetlightTasks()` are identical (duplicate)
2. `task()` uses `belongsTo` with `task_id`, but `streetlight_tasks` table uses `site_id` foreign key, not `task_id`
3. `task_id` in `streetlights` table is a `tinyint(4)`, not a foreign key reference
4. Comment on `tasks()` suggests "only task per site id" but uses `hasMany`

**Impact**:
- `task()` relationship likely broken (wrong relationship type and foreign key)
- Confusing code structure
- Potential bugs if `task()` is used

**Recommendation**: 
- Remove duplicate `tasks()` method
- Fix or remove `task()` method (likely should not exist based on schema)
- Verify actual relationship pattern: one site can have multiple tasks

---

### 1.5 Missing Foreign Key Column: Streetlight → Engineer
**Issue**: `Streetlight` model defines `engineer()` relationship but `engineer_id` is NOT in fillable array

**Evidence**:
- Relationship method exists: `belongsTo(User::class, 'engineer_id')`
- Fillable array does not include `engineer_id`
- Schema analysis shows `streetlights` table does NOT have `engineer_id` column

**Impact**:
- Relationship broken - column doesn't exist
- Engineers are assigned at task level (`streetlight_tasks.engineer_id`), not site level
- Misleading code

**Recommendation**: 
- Remove `engineer()` method from Streetlight model
- Access engineers via: `$streetlight->streetlightTasks->engineer`

---

### 1.6 Missing Relationship: Pole → Streetlight
**Issue**: `Pole` model defines `streetlight()` relationship but foreign key column is unclear

**Evidence**:
- Uses default foreign key assumption (`streetlight_id`)
- No `streetlight_id` in fillable array
- Pole has `task_id` which links to `StreetlightTask`, which has `site_id` linking to `Streetlight`
- Indirect relationship: Pole → Task → Site (Streetlight)

**Impact**:
- `streetlight()` relationship may not work if `streetlight_id` doesn't exist
- Accessing streetlight via `$pole->task->streetlight` is the correct path

**Recommendation**:
- Remove `streetlight()` method from Pole model
- Document indirect access pattern: `$pole->task->streetlight`

---

## 2. RELATIONSHIP FLOW ANALYSIS

### 2.1 Correct Relationship Chain

**Streetlights → StreetlightTasks** ✅
- `Streetlight::streetlightTasks()` → `hasMany(StreetlightTask::class, 'site_id')`
- `StreetlightTask::site()` → `belongsTo(Streetlight::class, 'site_id')`
- Foreign Key: `streetlight_tasks.site_id` → `streetlights.id`
- Status: **CORRECT**

**StreetlightTasks → Poles** ✅
- `StreetlightTask::poles()` → `hasMany(Pole::class, 'task_id')`
- `Pole::task()` → `belongsTo(StreetlightTask::class, 'task_id')`
- Foreign Key: `streelight_poles.task_id` → `streetlight_tasks.id`
- Status: **CORRECT**

**Poles → InventoryDispatch** ⚠️
- `InventoryDispatch::streetlightPole()` → `belongsTo(Pole::class, 'streetlight_pole_id')`
- `Pole` model MISSING reverse relationship
- Foreign Key: `inventory_dispatch.streetlight_pole_id` → `streelight_poles.id`
- Status: **PARTIALLY CORRECT** (missing reverse relationship)

**InventoryDispatch → InventoryStreetlight** ❌
- `InventoryDispatch::inventoryStreetLight()` → `belongsTo(InventroyStreetLightModel::class, 'inventory_id')`
- `InventroyStreetLightModel::dispatch()` → `hasOne(InventoryDispatch::class, 'serial_number', 'serial_number')`
- Uses `serial_number` for matching, not `inventory_id`
- Status: **INCONSISTENT/BROKEN**

**InventoryDispatch → Users (Vendor)** ✅
- `InventoryDispatch::vendor()` → `belongsTo(User::class)`
- Foreign Key: `inventory_dispatch.vendor_id` → `users.id`
- Vendor role: `UserRole::VENDOR = 3`
- Status: **CORRECT** (but should use enum in queries, not hardcoded 3)

**StreetlightTasks → Users (Vendor)** ✅
- `StreetlightTask::vendor()` → `belongsTo(User::class, 'vendor_id')`
- Foreign Key: `streetlight_tasks.vendor_id` → `users.id`
- Status: **CORRECT**

**Poles → Users (Vendor)** ✅
- `Pole::vendor()` → `belongsTo(User::class, 'vendor_id')`
- Foreign Key: `streelight_poles.vendor_id` → `users.id`
- Status: **CORRECT**

---

## 3. DATA FLOW OBSERVATIONS

### 3.1 Inventory Flow Path

**Stock Entry**:
1. Items received → `inventory_streetlight` table
   - Contains: `serial_number`, `quantity`, `item_code`, `rate`, etc.
   - Tracks stock availability per project/store

**Dispatch**:
2. Items dispatched to vendor → `inventory_dispatch` table
   - Links via: `serial_number` (not foreign key)
   - Sets: `vendor_id` (role=3), `isDispatched=true`
   - `inventory_streetlight.quantity` decremented

**Consumption**:
3. Items consumed on pole → `inventory_dispatch` updated
   - Sets: `streetlight_pole_id`, `is_consumed=true`
   - Links dispatch record to specific pole

**Observation**: 
- No direct foreign key constraint between `inventory_dispatch` and `inventory_streetlight`
- Relationship maintained via `serial_number` (string matching)
- Risk: Serial number mismatches not caught by database constraints

---

### 3.2 Vendor Assignment Flow

**Multiple Vendor Assignment Points**:
1. `streetlight_tasks.vendor_id` - Vendor assigned to task
2. `streelight_poles.vendor_id` - Vendor assigned to pole
3. `inventory_dispatch.vendor_id` - Vendor who received inventory

**Observation**:
- Vendor can be assigned at task level
- Vendor can be assigned at pole level (may differ from task vendor)
- Inventory dispatched to vendor (may differ from pole/task vendor)
- Potential for vendor mismatch between task, pole, and dispatch

**Current Code Pattern** (from PoleController):
```php
$poleDistrict = $pole->task && $pole->task->streetlight ? $pole->task->streetlight->district : null;
```

Uses indirect path: Pole → Task → Streetlight (site)

---

## 4. CODE QUALITY OBSERVATIONS

### 4.1 Hardcoded Role Values
**Issue**: User queries should use `UserRole::VENDOR` enum instead of hardcoded `role=3`

**Current Pattern** (found in codebase):
- Uses `role=3` or `role = 3` in queries
- Should use: `where('role', UserRole::VENDOR->value)` or `where('role', UserRole::VENDOR)`

**Files Affected**:
- `app/Services/Performance/PerformanceService.php` (lines 81, 129, 162, 231)
- `app/Services/Task/TaskManagementService.php` (line 582)
- `app/Services/Dashboard/DashboardService.php` (lines 65, 109, 139)
- `app/Repositories/User/UserRepository.php` (line 83)

**Recommendation**: Replace all hardcoded role values with enum constants per `.cursorrules`

---

### 4.2 Missing Relationship Documentation
**Issue**: Complex relationships lack clear documentation

**Observation**: 
- Multiple indirect relationship paths (Pole → Task → Site)
- Some relationships work via intermediate models
- Not immediately clear which relationships are direct vs indirect

**Recommendation**: 
- Add PHPDoc comments to relationship methods
- Document indirect access patterns
- Create relationship diagram/documentation

---

## 5. SCHEMA OBSERVATIONS

### 5.1 Foreign Key Constraints
**Verified Foreign Keys** (from schema analysis):
- ✅ `streetlight_tasks.site_id` → `streetlights.id` (CASCADE)
- ✅ `streetlight_tasks.vendor_id` → `users.id` (SET NULL)
- ✅ `streetlight_tasks.engineer_id` → `users.id` (SET NULL)
- ✅ `streelight_poles.task_id` → `streetlight_tasks.id` (implied)
- ✅ `streelight_poles.vendor_id` → `users.id` (SET NULL)
- ✅ `inventory_dispatch.vendor_id` → `users.id` (implied)
- ✅ `inventory_dispatch.streetlight_pole_id` → `streelight_poles.id` (implied)

**Missing Foreign Keys**:
- ❌ `inventory_dispatch.inventory_id` → `inventory_streetlight.id` (if this column exists)
- ❌ `inventory_dispatch` → `inventory_streetlight` via `serial_number` (cannot enforce with FK)

---

### 5.2 Table Naming Consistency
**Observation**:
- Most tables use singular: `streetlight`, `streetlight_task` (model names)
- Table names use plural: `streetlights`, `streetlight_tasks`
- Exception: `streelight_poles` (typo + plural)

---

## 6. SUMMARY OF RECOMMENDATIONS

### High Priority:
1. ✅ **Add missing relationship**: `Pole::inventoryDispatches()` → `hasMany(InventoryDispatch::class, 'streetlight_pole_id')`
2. ✅ **Fix InventoryDispatch relationship**: Verify `inventory_id` column or change to use `serial_number`
3. ✅ **Fix/Remove Streetlight::task()**: Incorrect relationship definition
4. ✅ **Remove Streetlight::engineer()**: Column doesn't exist in table
5. ✅ **Replace hardcoded role=3**: Use `UserRole::VENDOR` enum

### Medium Priority:
6. **Remove duplicate Streetlight::tasks()**: Keep only `streetlightTasks()`
7. **Verify/Remove Pole::streetlight()**: If direct FK doesn't exist, remove or document indirect access
8. **Add PHPDoc to relationships**: Document indirect vs direct relationships
9. **Fix StreetlightTask::pole()**: Remove incorrect belongsTo method (tasks have many poles)

---

## 7. VERIFIED WORKING RELATIONSHIPS

These relationships are correctly defined and working:

✅ **Streetlight → StreetlightTask** (via `site_id`)
✅ **StreetlightTask → Pole** (via `task_id`)
✅ **StreetlightTask → User (Vendor)** (via `vendor_id`)
✅ **StreetlightTask → User (Engineer)** (via `engineer_id`)
✅ **StreetlightTask → Project** (via `project_id`)
✅ **Pole → User (Vendor)** (via `vendor_id`)
✅ **InventoryDispatch → User (Vendor)** (via `vendor_id`)
✅ **InventoryDispatch → Project** (via `project_id`)
✅ **InventoryDispatch → Store** (via `store_id`)
✅ **InventoryStreetlight → Project** (via `project_id`)
✅ **InventoryStreetlight → Store** (via `store_id`)

---

**End of Observations**


