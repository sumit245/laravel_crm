# API Documentation

This document provides a detailed reference for the API endpoints, including request methods, URLs, parameters, and validation rules.

## 1. Authentication

### Login
*   **Endpoint**: `POST /login`
*   **Description**: Authenticates a user and returns an access token along with user details and associated projects.
*   **Parameters**:
    *   `email` (string, required): User's email address.
    *   `password` (string, required): User's password.
*   **Response**: JSON object containing `token`, `user` details, and `projects`.

---

## 2. Staff Management

### List Staff
*   **Endpoint**: `GET /staff`
*   **Description**: Retrieves a list of users with role ID 1 (typically Site Engineers/Admins).

### Upload Avatar
*   **Endpoint**: `POST /staff/upload-avatar/{id}`
*   **Description**: Uploads a profile picture for a specific user.
*   **Parameters**:
    *   `image` (file, required): Image file (jpeg, png, jpg, gif). Max size: 2048KB.
*   **Note**: Files are uploaded to S3.

### Get Performance
*   **Endpoint**: `GET /staff/get-performance/{user_id}`
*   **Description**: Calculates and returns performance metrics for the staff member based on assigned tasks (Pending, Completed, Backlogs).

---

## 3. Vendor Management

### List Vendors
*   **Endpoint**: `GET /vendor`
*   **Description**: Retrieves a list of all users with the Vendor role.

### Create Vendor
*   **Endpoint**: `POST /vendor`
*   **Description**: Creates a new vendor account.
*   **Parameters**:
    *   `name`, `email`, `password`, `username`, `firstName`, `lastName` (strings).
    *   `contactNo`, `address`.

### Update Vendor
*   **Endpoint**: `PUT /vendor/{id}`
*   **Description**: Updates an existing vendor's information.
*   **Parameters**:
    *   `firstName`, `lastName` (string, nullable).
    *   `email` (email, nullable).
    *   `password` (string, min 8 chars, nullable).
    *   `contactNo`, `address` (string, nullable).

---

## 4. Task Management

### List Tasks
*   **Endpoint**: `GET /task`
*   **Description**: Retrieves all tasks with related project, site, and vendor data.

### Create Task
*   **Endpoint**: `POST /task`
*   **Description**: Creates a new field task.
*   **Parameters**:
    *   `sites` (array, required): List of site IDs.
    *   `activity` (string, required): Description of activity.
    *   `engineer_id` (integer, required): ID of the engineer (must exist in users table).
    *   `start_date` (date, required).
    *   `end_date` (date, required): Must be after or equal to `start_date`.

### Get Task Details
*   **Endpoint**: `GET /task/{id}`
*   **Parameters**:
    *   `project_type` (query param): Set to `1` to fetch `StreetlightTask`, otherwise fetches standard `Task`.

### Update Task
*   **Endpoint**: `PUT /task/{id}`
*   **Description**: Updates task details and optionally site location data.
*   **Parameters**:
    *   `image` (file/array): Single or multiple files to upload to S3.
    *   `survey_lat`, `survey_long` (numeric): Updates site's survey location.
    *   `lat`, `long` (numeric): Updates site's actual location.

### Approve Task
*   **Endpoint**: `POST /tasks/{id}/approve`
*   **Description**: Marks a task as 'Completed'.

### Get Installable Poles
*   **Endpoint**: `GET /get_installable_pole/{ward}`
*   **Description**: Returns a list of available pole numbers (1-20) for a specific ward that haven't been assigned yet.

### Submit Streetlight Task (Survey/Installation)
*   **Endpoint**: `POST /streetlight/tasks/update`
*   **Description**: Submits data for pole survey or installation. Handles logic for creating/updating `Pole` records and linking inventory.
*   **Parameters**:
    *   `task_id` (required, exists in streetlight_tasks).
    *   `complete_pole_number` (string, required).
    *   `ward_name` (string, nullable).
    *   `isSurveyDone` (boolean string, 'true'/'false').
    *   `isInstallationDone` (boolean string, 'true'/'false').
    *   `lat`, `lng` (numeric).
    *   `survey_image`, `submission_image` (array of files): Uploaded to S3.
    *   *Installation specific*:
        *   `luminary_qr`, `panel_qr`, `battery_qr` (string): QR codes/Serial numbers.
        *   `sim_number` (string).
*   **Validation**: Checks if inventory serial numbers belong to the correct district (Store vs Pole location mismatch).

### Sync to RMS
*   **Endpoint**: `POST /send-to-rms`
*   **Parameters**:
    *   `filter` (string, optional): 'all', 'surveyed', or 'installed'.

---

## 5. Streetlight Management

### Get Engineer Tasks
*   **Endpoint**: `GET /streetlight/tasks/engineers`
*   **Parameters**: `id` (engineer's user ID).

### Get Vendor Tasks
*   **Endpoint**: `GET /streetlight/tasks/vendors`
*   **Parameters**: `id` (vendor's user ID).

### Location Data (Dropdowns)
*   **Endpoint**: `GET /blocks/{district}`: Returns blocks for a district.
*   **Endpoint**: `GET /panchayats/{block}`: Returns panchayats for a block (excluding assigned sites).

---

## 6. Inventory Management

### List Inventory
*   **Endpoint**: `GET /inventories`
*   **Description**: Returns all inventory items.

### Create Inventory
*   **Endpoint**: `POST /inventories`
*   **Parameters**: `productName`, `brand`, `unit`, `initialQuantity`, `quantityStock`.

### Dispatch Inventory
*   **Endpoint**: `POST /inventory/dispatch/vendor`
*   **Description**: Dispatches inventory items to a vendor for a specific project.
*   **Parameters**:
    *   `vendor_id` (required).
    *   `project_id` (required).
    *   `store_id` (required).
    *   `store_incharge_id` (required).
    *   `items` (array, required): List of objects containing `inventory_id` and `quantity`.

### Replace Item
*   **Endpoint**: `POST /replace-item` (Implicitly found in InventoryController)
*   **Description**: Replaces a faulty item serial number with a new one.
*   **Parameters**:
    *   `old_serial_number` (required).
    *   `new_serial_number` (required).
    *   `auth_code` (required): Must match server env `REPLACEMENT_AUTH_CODE`.
