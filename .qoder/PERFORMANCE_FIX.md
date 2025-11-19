# Performance System Fix - User Relationship Queries

## Problem Identified
The performance system was showing "No performance data available" and "No project managers found" even when project had managers, engineers, and vendors with data.

## Root Cause
The original queries were too restrictive:
1. Only looked for users with `project_id` field set
2. Didn't account for users who are linked to projects through tasks
3. Didn't handle cases where `manager_id` or `site_engineer_id` might not be set but users still have tasks

## Solution Implemented

### 1. Enhanced User Model Relationships
Added missing task relationships to `app/Models/User.php`:

```php
// Rooftop task relationships
public function managerTasks()
public function engineerTasks()
public function vendorTasks()

// Streetlight task relationships
public function streetlightTasks()
public function streetlightEngineerTasks()
public function streetlightVendorTasks()
```

### 2. Improved Query Logic in PerformanceService

**Before:**
```php
$managers = User::where('role', 2)
    ->where('project_id', $projectId)
    ->get();
```

**After:**
```php
$managers = User::where('role', 2)
    ->where(function($query) use ($projectId, $isStreetlight) {
        $query->where('project_id', $projectId)
            ->orWhereHas($isStreetlight ? 'streetlightTasks' : 'managerTasks', function($q) use ($projectId) {
                $q->where('project_id', $projectId);
            });
    })
    ->distinct()
    ->get();
```

This now finds managers in TWO ways:
1. **Direct assignment**: Users with `project_id = 11`
2. **Task-based**: Users who have tasks in `project_id = 11`

### 3. Applied Same Logic to All Hierarchies

**Engineers Query:**
- Find by `manager_id` AND `project_id` OR
- Find by having tasks with `manager_id` in the project

**Vendors Query:**
- Find by `site_engineer_id` AND `project_id` OR  
- Find by having tasks with `engineer_id` in the project

**Added `.distinct()`** to prevent duplicate users if they match both conditions.

## Files Modified

1. **app/Services/Performance/PerformanceService.php**
   - Updated `getAdminHierarchy()` - Line 47
   - Updated `getEngineerHierarchy()` - Line 78
   - Updated `getEngineersByManager()` - Line 108
   - Updated `getVendorsByManager()` - Line 149

2. **app/Models/User.php**
   - Added 6 new task relationship methods - Lines 127-157

## How It Works Now

### For Project 11 (Streetlight):

**Admin Login:**
1. Finds all Project Managers who either:
   - Have `project_id = 11` OR
   - Have `streetlightTasks` where `project_id = 11`
2. For each manager, finds their engineers and vendors using same logic
3. Calculates performance metrics based on their tasks

**Project Manager Login:**
1. Finds all Engineers who either:
   - Have `manager_id = [your_id]` AND `project_id = 11` OR
   - Have tasks with `manager_id = [your_id]` AND `project_id = 11`
2. Finds all Vendors similarly
3. Shows hierarchical performance

**Site Engineer Login:**
1. Finds all Vendors who either:
   - Have `site_engineer_id = [your_id]` AND `project_id = 11` OR
   - Have tasks with `engineer_id = [your_id]` AND `project_id = 11`

## Testing Steps

1. **Clear Cache:**
   ```bash
   php artisan cache:clear
   php artisan view:clear
   ```

2. **Test Admin View:**
   - Login as Admin
   - Select Project 11 (Streetlight)
   - Go to Dashboard or `/performance`
   - Should now see Project Managers with their data

3. **Test Date Filters:**
   - Try "All Time" (should show historical data)
   - Try "Today" (shows only today's activity)
   - Try "This Month" (shows current month)

4. **Test Drill-Down:**
   - Click on a Project Manager card
   - Should expand to show Engineers
   - Click on an Engineer
   - Should show their Vendors

## Expected Output

**Dashboard View:**
```
Performance Overview
[Date Filter Dropdown] [View Detailed Performance Button]

Project Managers
┌─────────────────────────────────┐
│ 👤 John Doe (PM)          85%   │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │
│ 📍 Total Poles: 500             │
│ 🔍 Surveyed: 450                │
│ 💡 Installed: 425               │
│ ⏰ Backlog: 5                   │
│                                 │
│ [View Team ▼]                   │
└─────────────────────────────────┘
```

**Performance Page:**
```
📊 Performance Overview
Project: Streetlight Project

🏆 Top Performers
[1st] [2nd] [3rd] with medals and gradient cards

📋 Project Managers Performance
[Expandable cards showing full hierarchy]
```

## Data Flow

```
Request to /performance
    ↓
PerformanceController::index()
    ↓
PerformanceService::getHierarchicalPerformance()
    ↓
Check user role (0=Admin, 2=PM, 1=Engineer)
    ↓
getAdminHierarchy() OR getManagerHierarchy() OR getEngineerHierarchy()
    ↓
Query users with TWO conditions (OR logic):
  1. Direct project_id assignment
  2. Has tasks in the project
    ↓
For each user, calculate metrics:
  - Query their tasks (StreetlightTask for project 11)
  - Count poles (from task->site->total_poles)
  - Count surveyed poles (from Pole table where isSurveyDone=1)
  - Count installed poles (from Pole table where isInstallationDone=1)
  - Calculate performance percentage
    ↓
Return hierarchical array
    ↓
Blade view renders based on role
```

## Performance Metrics Calculation

### For Streetlight Projects:
```php
$totalPoles = sum of task->site->total_poles
$surveyedPoles = count of poles where isSurveyDone = 1
$installedPoles = count of poles where isInstallationDone = 1
$performance = ($installedPoles / $totalPoles) * 100
```

### For Rooftop Projects:
```php
$totalTasks = count of all tasks
$completedTasks = count of tasks where status = 'Completed'
$performance = ($completedTasks / $totalTasks) * 100
```

## Cache Behavior

- Performance data cached for **15 minutes** (900 seconds)
- Cache key includes: `userId`, `projectId`, and `filters` (date range)
- Different cache for each combination
- Clear cache with: `php artisan cache:clear`

## Troubleshooting

### Still shows "No data":

1. **Check if users have tasks:**
   ```sql
   SELECT id, firstName, lastName, role, manager_id, site_engineer_id, project_id 
   FROM users 
   WHERE project_id = 11;
   
   SELECT manager_id, engineer_id, vendor_id, COUNT(*) 
   FROM streetlight_tasks 
   WHERE project_id = 11 
   GROUP BY manager_id, engineer_id, vendor_id;
   ```

2. **Verify role values:**
   - 0 = Admin
   - 1 = Site Engineer  
   - 2 = Project Manager
   - 3 = Vendor

3. **Clear all caches:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   php artisan route:clear
   ```

4. **Check error logs:**
   - `storage/logs/laravel.log`
   - Browser console for JavaScript errors

### Performance is 0%:

1. Check if poles exist:
   ```sql
   SELECT COUNT(*) FROM poles WHERE task_id IN (
       SELECT id FROM streetlight_tasks WHERE project_id = 11
   );
   ```

2. Check if poles are marked:
   ```sql
   SELECT 
       COUNT(*) as total,
       SUM(isSurveyDone) as surveyed,
       SUM(isInstallationDone) as installed
   FROM poles 
   WHERE task_id IN (SELECT id FROM streetlight_tasks WHERE project_id = 11);
   ```

## Next Steps

If data still doesn't appear:
1. Share the output of the SQL queries above
2. Check if `StreetlightTask` model exists and has proper relationships
3. Verify database table names match the models
4. Check if there are any PHP errors in `storage/logs/laravel.log`

## Support

The fix handles edge cases where:
- Users might not have `project_id` set but have tasks
- `manager_id` or `site_engineer_id` might be NULL
- Multiple ways to link users to projects (direct or through tasks)
- Prevents duplicate users with `.distinct()`

All queries now use **OR logic** to find users through multiple paths, making the system more flexible and resilient to incomplete data relationships.
