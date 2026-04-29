# Activity Logger API & Integration Guide

## Overview

The `ActivityLogger` is a core architectural component used across the CRM to strictly track system mutations. As part of our auditability improvements, **all write operations** (Create, Update, Delete, Import, Dispatch, Push) across the Web and API controllers must interface with this service to provide an organized and detailed history. 

Read-only operations (like `search` and `index`) do not trigger the logger to prevent database bloat.

---

## 1. Using the Logger in Controllers

To use the Activity Logger, inject `App\Services\Logging\ActivityLogger` into your controller's constructor.

### Constructor Injection

```php
use App\Services\Logging\ActivityLogger;

class StaffController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}
}
```

---

## 2. Standard Logging Operations

The `log()` method is structured to capture semantic meaning alongside raw data. 

**Signature:**
```php
public function log(string $module, string $action, $entity = null, array $metadata = [])
```

### Parameter Reference

- **`$module`** *(string)*: The logical area being affected. E.g., `'inventory'`, `'site'`, `'task'`, `'user'`.
- **`$action`** *(string)*: The type of mutation. E.g., `'created'`, `'updated'`, `'deleted'`, `'imported'`, `'dispatched'`, `'pushed_to_rms'`.
- **`$entity`** *(mixed)*: The Eloquent Model affected. Passing this automatically associates the log entry with the specific database record for targeted history views. Pass `null` for bulk actions where a single model doesn't apply.
- **`$metadata`** *(array)*: Additional context. Must contain a `'description'` key mapping to a human-readable string. Can also include custom data payloads via `'extra'`.

---

## 3. Example Use Cases

### Creating a Record
When a new model is created, pass the freshly instantiated model as the entity.

```php
$staff = User::create($validatedData);

$this->activityLogger->log('user', 'created', $staff, [
    'description' => "Created new staff member {$staff->firstName} {$staff->lastName} ({$staff->username})"
]);
```

### Deleting a Record
When deleting, especially for single items, record the name or identifying field in the description, as the entity itself will no longer exist in the active table.

```php
$staffName = "{$staff->firstName} ({$staff->username})";
$staff->delete();

$this->activityLogger->log('user', 'deleted', null, [
    'description' => "Deleted staff member {$staffName}"
]);
```

### Bulk Operations
For imports or mass deletes, use a `null` entity and describe the operation scale.

```php
$count = User::whereIn('id', $ids)->delete();

$this->activityLogger->log('user', 'deleted', null, [
    'description' => "Bulk deleted {$count} staff members"
]);
```

---

## 4. Capturing State Changes (The Diff Tool)

For updates, we must track exactly what changed to satisfy auditing requirements. The `ActivityLogger` provides a `diff()` method to automate before and after state capture. 

**Very Important:** You must call `diff()` **before** you execute `$model->update()`.

### Example: Accurate Update Tracking

```php
// 1. Capture the 'before' state
$beforeAfter = $this->activityLogger->diff($task);

// 2. Perform the update
$task->update($validData);

// 3. Log the update, merging the generic description with the diff array
$this->activityLogger->log('task', 'updated', $task, array_merge([
    'description' => "Updated streetlight target #{$id}"
], $beforeAfter));
```

The `$beforeAfter` array will automatically populate the `old_values` and `new_values` of the JSON structure within the database.

---

## 5. Scope of Current Coverage

The `ActivityLogger` is fully integrated into **all controllers with write operations** (16 total).

### Web Controllers (9)

| Controller | Write Methods Logged |
|---|---|
| `StoreController` | store, destroy |
| `VendorController` | store, update, destroy, bulkDelete, import, bulkAssignProjects, uploadAvatar |
| `DeviceController` | import |
| `CandidateController` | importCandidates, uploadDocuments, destroy |
| `MeetController` | store, update, destroy, storeDiscussionPoint, updateDiscussionPointStatus, storeDiscussionPointUpdate, scheduleFollowUp, deleteDiscussionPoint, removeAttendee, deleteFollowUp |
| `WhiteboardController` | store/updateOrCreate |
| `BackupController` | create (backup), delete |
| `InventoryController` | store, update, destroy, dispatch |
| `ProjectsController` | store, update, destroy |

> Plus: `SiteController`, `PoleController`, `StaffController`, `TasksController`, `RMSController`, `ConvenienceController`

### API Controllers (7)

| Controller | Write Methods Logged |
|---|---|
| `API\TaskController` | store, update, destroy, approveTask, submitStreetlightTasks |
| `API\ConveyanceController` | store (TADA), storeConveyance |
| `API\StreetlightController` | store, destroy, submitTask |
| `API\ProjectController` | create, update, destroy |
| `API\VendorController` | create, update, destroy, uploadAvatar |
| `API\PreviewController` | submitFinal, bulkUpdate |
| `API\InventoryController` | store, update, dispatch |

> Plus: `API\SiteController`, `API\StaffController`

### Read-Only Controllers (10 — Correctly excluded)

`Controller`, `HomeController`, `CodeDocController`, `JICRController`, `PerformanceController`, `PerformanceDebugController`, `QueueProcessorController`, `RmsSyncController`, `API\DropdownController`, `API\LoginController`

## Best Practices

- **Never bypass the Service**: Always use the constructor dependency injection. `ActivityLogger` handles capturing headers, `batch_id`, the authenticated user, and the current IP address internally.
- **Do not log Read endpoints**: `index`, `search`, and `show` must remain log-free to avoid storage bloat.
- **Use meaningful descriptions**: A description like "Updated task status to Complete" is significantly more valuable than "Task updated". 
