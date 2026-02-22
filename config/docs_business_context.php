<?php

/**
 * Business Context Configuration for Code Documentation
 *
 * Maps each class to its real-world business purpose, data flow,
 * and dependencies so the auto-generated docs show meaningful
 * descriptions rather than generic "Extends Controller" text.
 */

return [

    // ─── WEB CONTROLLERS ──────────────────────────────────────

    'HomeController' => [
        'business_summary' => 'Main Dashboard & Analytics Hub — powers the primary dashboard that project managers and admins see after login. Aggregates real-time performance data (pole survey/installation progress, district-wise breakdowns, top performers), meeting analytics, and TA/DA financial overviews. Supports date-range filtering and Excel export of dashboard KPIs.',
        'data_flow' => 'User Login → Auth Middleware → Dashboard filters (project, date range) → DashboardAnalyticsService → Aggregated DB queries (Poles, Tasks, Meetings, TADA) → Blade View / AJAX JSON / Excel Export',
        'depends_on' => ['DashboardAnalyticsService', 'Project', 'User', 'Pole', 'Streetlight', 'Task'],
        'business_domain' => 'Dashboard & Reporting',
    ],

    'StoreController' => [
        'business_summary' => 'Warehouse / Store Management — each project has physical stores (warehouses) where inventory items (Panels, Luminaries, Batteries, Structures) are received and tracked. This controller handles store creation, viewing store inventory with real-time stock-vs-dispatched metrics, server-side paginated DataTable for 100k+ inventory rows, and Excel export of filtered inventory data.',
        'data_flow' => 'Admin creates Store → Inventory imported via GRN Excel → Store show page aggregates stock metrics (initial value, in-store, dispatched) → DataTable AJAX feeds paginated rows with dispatch status → Export streams filtered rows to Excel',
        'depends_on' => ['Stores', 'Inventory', 'InventroyStreetLightModel', 'InventoryDispatch', 'InventoryExport', 'User', 'Project'],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'InventoryController' => [
        'business_summary' => 'Inventory Lifecycle Management — handles the full lifecycle of inventory items: receiving stock via Excel GRN imports (both rooftop and streetlight types), adding individual items with serial number & SIM uniqueness validation, dispatching items to field vendors (decrementing store quantity), tracking dispatch history, and supporting item return/replacement flows. Item codes: SL01 (Panel), SL02 (Luminary), SL03 (Battery), SL04 (Structure).',
        'data_flow' => 'GRN Excel Upload → Validation (serial/SIM uniqueness) → InventoryService → DB Insert → Dispatch: Select serials → Validate stock → Create InventoryDispatch records → Decrement quantity → Log history → Return/Replace: Reverse dispatch flow',
        'depends_on' => ['InventoryServiceInterface', 'InventoryHistoryService', 'ActivityLogger', 'InventoryDispatch', 'InventroyStreetLightModel', 'Inventory', 'Project', 'Stores'],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'TasksController' => [
        'business_summary' => 'Target / Task Assignment Management — manages work targets assigned to field staff. Targets are created in bulk by selecting sites/panchayats and assigning engineers, vendors, and project managers. Supports both streetlight and rooftop project types. Key business rules: completed tasks cannot be reassigned (historical data preservation), date extensions require reason logging, and vendor ward-conflict checking prevents duplicate work assignments.',
        'data_flow' => 'Admin/PM selects project → Choose sites → Assign engineer + vendor + PM → TaskService.createBulkTasks() → DB: StreetlightTask or Task record → Edit: Ward conflict check (AJAX) → Update with audit logging → Excel export via TasksExport',
        'depends_on' => ['TaskServiceInterface', 'ActivityLogger', 'Project', 'StreetlightTask', 'Task', 'Pole', 'StoreTaskRequest', 'UpdateTaskRequest'],
        'business_domain' => 'Field Operations',
    ],

    'ProjectsController' => [
        'business_summary' => 'Project Lifecycle Management — central hub for managing solar energy projects. Each project represents a government contract (e.g., streetlight installation in a district). Handles project creation, stakeholder assignment (PMs, engineers, vendors), target management (bulk create/delete/reassign with async progress tracking for large deletions), and project-level analytics. The project show page is the operational command center showing sites, tasks, inventory, and staff.',
        'data_flow' => 'Admin creates Project → Assign PMs/Engineers/Vendors via project_user pivot → Import sites/targets from Excel → Show: Aggregates all sub-modules (sites, tasks, inventory, staff) → Async bulk operations with job queue and progress polling',
        'depends_on' => ['Project', 'User', 'Site', 'Streetlight', 'StreetlightTask', 'Task', 'Pole', 'InventoryDispatch', 'ActivityLogger', 'TargetDeletionService'],
        'business_domain' => 'Project Management',
    ],

    'MeetController' => [
        'business_summary' => 'Meeting Minutes & Discussion Tracking — manages formal meetings between project stakeholders (client, government officials, internal teams). Captures attendees, discussion points with status tracking (Open → In Progress → Resolved), follow-up scheduling, and discussion point updates over time. Supports PDF and Excel export of meeting minutes for formal record-keeping.',
        'data_flow' => 'Create Meeting → Add attendees from staff → Add discussion points → Track status changes → Schedule follow-ups → Export PDF/Excel minutes → WhatsApp notification to attendees',
        'depends_on' => ['Meet', 'DiscussionPoint', 'DiscussionPointUpdates', 'FollowUp', 'User', 'WhatsappHelper', 'DomPDF'],
        'business_domain' => 'Meetings & Collaboration',
    ],

    'PoleController' => [
        'business_summary' => 'Streetlight Pole Record Management — manages individual pole records after field teams survey and install them. Each pole has geo-coordinates, photos (before/after installation), serial numbers for attached equipment (Panel, Luminary, Battery), and RMS push status. Supports serial replacement (when faulty equipment is swapped), bulk delete, and bulk push to the government\'s Remote Monitoring System (RMS).',
        'data_flow' => 'Field app surveys pole → API creates Pole record → Web: Edit pole details → Replace serial (old inventory returned, new dispatched) → Upload images to S3 → Bulk push to RMS API → Mark as pushed',
        'depends_on' => ['Pole', 'InventoryDispatch', 'InventroyStreetLightModel', 'InventoryService', 'InventoryHistoryService', 'ActivityLogger'],
        'business_domain' => 'Field Operations',
    ],

    'SiteController' => [
        'business_summary' => 'Site / Panchayat Management — manages project sites (locations where work happens). For streetlight projects, a "site" represents a panchayat with a ward structure and target pole count. Sites are imported from Excel with district-panchayat-ward hierarchy. Supports pole imports per site, bulk operations, and downloadable import templates.',
        'data_flow' => 'Excel import → Parse district/panchayat/ward/pole count → Create Streetlight or Site records → Assign via Tasks → Show: Display poles underneath site → Bulk delete/import operations',
        'depends_on' => ['Site', 'Streetlight', 'Pole', 'StreetlightTask', 'City', 'Project', 'SiteImport', 'StreetlightImport', 'SitePoleImport', 'ActivityLogger'],
        'business_domain' => 'Site Management',
    ],

    'StaffController' => [
        'business_summary' => 'Employee & Field Staff Management — manages all staff roles: Admin, Project Managers, Site Engineers, and Vendors. Handles onboarding (create/import from Excel), profile management (avatar upload, password change, mobile number update via OTP), and performance views. The staff show page aggregates a staff member\'s assigned tasks, poles worked on, inventory dispatched, and panchayat-level progress. Supports bulk operations and WhatsApp OTP for secure mobile changes.',
        'data_flow' => 'Admin creates/imports staff → Assign role + project → Staff profile: Show assigned tasks, poles, inventory → OTP flow: Request → WhatsApp send → Verify → Update mobile → Vendor/Engineer data: Aggregate panchayat-wise pole progress → Push to RMS / Delete panchayat with inventory rollback',
        'depends_on' => ['User', 'Project', 'Pole', 'StreetlightTask', 'InventoryDispatch', 'DiscussionPoint', 'WhatsappHelper', 'StaffImport', 'ActivityLogger'],
        'business_domain' => 'Staff & HR',
    ],

    'ConvenienceController' => [
        'business_summary' => 'Travel Allowance & Expense (TA/DA) Management — manages travel conveyance claims submitted by field staff from the mobile app. Staff submit daily travel records with vehicle type, distance, and fare. Admins can accept/reject claims individually or in bulk. Also manages TA/DA settings: vehicle master (types with per-km rates), city categories (A/B/C tier for daily allowance rates), staff category assignments, and fare configuration.',
        'data_flow' => 'Field staff submits travel → Conveyance record created → Admin reviews → Accept/Reject (individually or bulk) → TADA aggregate view → Settings: Manage vehicles, city categories, daily allowance rates, staff categories → View Bills for disbursement',
        'depends_on' => ['Conveyance', 'Tada', 'Vehicle', 'UserCategory', 'City', 'dailyfare', 'travelfare', 'Journey', 'HotelExpense', 'User', 'ActivityLogger'],
        'business_domain' => 'Finance & Expense',
    ],

    'CandidateController' => [
        'business_summary' => 'Recruitment & Candidate Management — manages job candidate records during hiring. Supports candidate registration, document uploads, interview scheduling, status tracking (Applied → Shortlisted → Interviewed → Hired/Rejected), and search/filter capabilities.',
        'data_flow' => 'Create candidate → Upload documents → Schedule interview → Update status → Filter/search candidates → Excel export',
        'depends_on' => ['Candidate', 'Project', 'User'],
        'business_domain' => 'HR & Recruitment',
    ],

    'BackupController' => [
        'business_summary' => 'Data Backup & Restoration — provides database backup and data export capabilities for disaster recovery and data portability. Supports full database dumps and selective table exports.',
        'data_flow' => 'Admin triggers backup → Database dump → Download file → Restore: Upload backup → Parse → Restore tables',
        'depends_on' => ['DataTransformationService'],
        'business_domain' => 'System Administration',
    ],

    'ActivityLogController' => [
        'business_summary' => 'Audit Trail & Activity Logging — displays a chronological log of all important actions performed in the system (inventory imports, dispatches, task assignments, etc.). Provides accountability and traceability for operations.',
        'data_flow' => 'System actions trigger ActivityLogger → ActivityLog records created → Controller fetches paginated logs → Display in timeline view',
        'depends_on' => ['ActivityLog', 'ActivityLogger'],
        'business_domain' => 'Audit & Compliance',
    ],

    'DeviceController' => [
        'business_summary' => 'Mobile Device Import Management — handles bulk import of device records from Excel files. Used for tracking field devices (tablets, phones) assigned to field staff.',
        'data_flow' => 'Excel upload → Parse device records → Validate → Store in database',
        'depends_on' => ['Project'],
        'business_domain' => 'Asset Management',
    ],

    'JICRController' => [
        'business_summary' => 'Joint Inspection & Completion Report (JICR) — generates official completion reports for government inspection. These reports document installed poles, equipment details, and photographs for formal project sign-off and billing.',
        'data_flow' => 'Select project/panchayat → Fetch completed poles with photos → Generate JICR report → Download as PDF/Excel → Submit to government authority',
        'depends_on' => ['Pole', 'Streetlight', 'StreetlightTask', 'Project', 'User'],
        'business_domain' => 'Compliance & Reporting',
    ],

    'RMSController' => [
        'business_summary' => 'Remote Monitoring System Integration — pushes pole installation data to the government\'s RMS portal. Tracks push status, handles API communication, and logs responses for each pole. Supports individual and bulk push operations.',
        'data_flow' => 'Select poles → Prepare payload (coordinates, serial numbers, photos) → POST to RMS API → Log response → Mark poles as pushed → Track push history',
        'depends_on' => ['Pole', 'RmsPushLog', 'RemoteApiHelper', 'Project'],
        'business_domain' => 'Government Integration',
    ],

    'PerformanceController' => [
        'business_summary' => 'Staff Performance Analytics — calculates and displays performance metrics for engineers, vendors, and project managers. Tracks survey completion rates, installation progress, and comparative leaderboards.',
        'data_flow' => 'Select project + date range → Query poles/tasks per staff → Calculate completion percentages → Rank by performance → Display leaderboard → Export to Excel',
        'depends_on' => ['PerformanceService', 'User', 'Pole', 'Task', 'Project'],
        'business_domain' => 'Dashboard & Reporting',
    ],

    'PerformanceDebugController' => [
        'business_summary' => 'Performance Debugging Tool — diagnostic controller used to debug and verify performance calculation accuracy. Provides detailed breakdowns of how metrics are computed for troubleshooting discrepancies.',
        'data_flow' => 'Select staff member → Run performance queries with detailed logging → Compare calculated vs expected values → Display diagnostic output',
        'depends_on' => ['User', 'Pole', 'Task', 'Project'],
        'business_domain' => 'System Administration',
    ],

    'QueueProcessorController' => [
        'business_summary' => 'Background Job Processor — handles asynchronous job processing for long-running operations like bulk target deletion, large Excel imports, and RMS push batches. Provides progress tracking endpoints.',
        'data_flow' => 'Trigger async operation → Dispatch to queue → Worker processes chunks → Update progress in cache → Polling endpoint returns completion %',
        'depends_on' => ['ProcessPoleImportChunk', 'ProcessTargetDeletionChunk', 'TargetDeletionJob'],
        'business_domain' => 'System Administration',
    ],

    'VendorController' => [
        'business_summary' => 'Vendor Management — handles vendor-specific operations like viewing assigned inventory, tracking dispatched vs consumed items, and vendor performance summaries.',
        'data_flow' => 'List vendors for project → View vendor inventory (dispatched items) → Track consumption against poles',
        'depends_on' => ['User', 'InventoryDispatch', 'Project'],
        'business_domain' => 'Vendor Management',
    ],

    'WhiteboardController' => [
        'business_summary' => 'Collaborative Whiteboard / Notes — provides a shared notes/whiteboard feature for project teams to collaborate on ideas, action items, and quick notes during planning sessions.',
        'data_flow' => 'Create whiteboard → Add content → Share with team → Edit collaboratively',
        'depends_on' => ['Whiteboard', 'User'],
        'business_domain' => 'Meetings & Collaboration',
    ],

    // ─── AUTH CONTROLLERS ──────────────────────────────────────

    'LoginController' => [
        'business_summary' => 'User Authentication — handles login with role-based redirection. After successful login, admins see the main dashboard, while project managers are redirected to their assigned project. Supports standard web login with session management.',
        'data_flow' => 'Login form → Validate credentials → Create session → Role check → Redirect to dashboard or project page',
        'depends_on' => ['User', 'AuthenticatesUsers'],
        'business_domain' => 'Authentication',
    ],

    'RegisterController' => [
        'business_summary' => 'User Registration — handles new user creation with role assignment. Admin-only operation for creating new staff accounts with appropriate role assignments.',
        'data_flow' => 'Registration form → Validate input → Hash password → Create user record → Assign role → Redirect to staff list',
        'depends_on' => ['User', 'RegistersUsers'],
        'business_domain' => 'Authentication',
    ],

    // ─── API CONTROLLERS ──────────────────────────────────────

    'API\\LoginController' => [
        'business_summary' => 'Mobile App Authentication API — provides token-based authentication for the React Native field app. Handles login with device info tracking, token refresh, and logout (token revocation).',
        'data_flow' => 'POST /api/login → Validate credentials → Generate API token → Return user profile + permissions → Logout: Revoke token',
        'depends_on' => ['User'],
        'business_domain' => 'Mobile API',
    ],

    'API\\TaskController' => [
        'business_summary' => 'Field Task API — the primary API used by field engineers and vendors on the mobile app. Provides task listings filtered by assigned staff, pole survey submission (with GPS coordinates, photos), installation status updates, and SIM number capture. This is where real field work data enters the system.',
        'data_flow' => 'GET /api/tasks → Filter by user role → Return task list with poles → POST /api/survey → Validate GPS + photos → Create/Update Pole record → Upload images to S3 → POST /api/install → Mark pole as installed → Link inventory serial numbers',
        'depends_on' => ['StreetlightTask', 'Task', 'Pole', 'InventoryDispatch', 'User', 'Project'],
        'business_domain' => 'Mobile API',
    ],

    'API\\ConveyanceController' => [
        'business_summary' => 'Travel Expense Submission API — allows field staff to submit daily travel conveyance claims from the mobile app. Captures journey details (start/end locations, vehicle type, distance, fare), supports photo attachments for receipts, and returns claim history.',
        'data_flow' => 'POST /api/conveyance → Validate journey details → Calculate fare based on vehicle rate + distance → Create Conveyance record → Upload receipt photos → GET /api/conveyance/history → Return paginated claim history',
        'depends_on' => ['Conveyance', 'Tada', 'Vehicle', 'User'],
        'business_domain' => 'Mobile API',
    ],

    'API\\VendorController' => [
        'business_summary' => 'Vendor Inventory API — provides vendor-specific API for viewing dispatched inventory, marking items as consumed (installed on poles), and checking available stock. Used by the vendor mobile app during installation.',
        'data_flow' => 'GET /api/vendor/inventory → Filter by vendor + project → Return dispatched items → POST /api/vendor/consume → Link serial to pole → Mark as consumed',
        'depends_on' => ['InventoryDispatch', 'User', 'Pole', 'Project'],
        'business_domain' => 'Mobile API',
    ],

    'API\\DropdownController' => [
        'business_summary' => 'Reference Data API — provides dropdown/lookup data for the mobile app forms. Returns lists of states, cities, districts, and other reference data needed for form population.',
        'data_flow' => 'GET /api/states → Return state list → GET /api/cities?state_id=X → Return filtered city list',
        'depends_on' => ['State', 'City'],
        'business_domain' => 'Mobile API',
    ],

    'API\\PreviewController' => [
        'business_summary' => 'Pole Preview & Photo API — provides photo preview and pole detail viewing for the mobile app. Used during quality checks to review surveyed poles, their photos, and equipment details before marking as approved.',
        'data_flow' => 'GET /api/preview/{pole_id} → Return pole details with photo URLs → Display in mobile photo viewer → Support photo zoom and comparison',
        'depends_on' => ['Pole', 'StreetlightTask', 'Streetlight', 'Project'],
        'business_domain' => 'Mobile API',
    ],

    'API\\StaffController' => [
        'business_summary' => 'Staff Profile API — provides mobile app endpoints for staff profile viewing and updates. Allows field staff to view their profile, update avatar, and manage basic settings from the app.',
        'data_flow' => 'GET /api/staff/profile → Return user profile → PUT /api/staff/avatar → Upload new photo → Update profile',
        'depends_on' => ['User', 'Project'],
        'business_domain' => 'Mobile API',
    ],

    'API\\StreetlightController' => [
        'business_summary' => 'Streetlight Site Data API — provides panchayat/ward/pole data for the mobile app\'s task execution flow. Engineers use this to fetch their assigned sites, view target pole counts, and navigate to specific GPS locations for survey work.',
        'data_flow' => 'GET /api/streetlights → Filter by project + engineer → Return site list with pole counts → GET /api/streetlights/{id}/poles → Return pole list with survey status',
        'depends_on' => ['Streetlight', 'StreetlightTask', 'Pole', 'User', 'Project'],
        'business_domain' => 'Mobile API',
    ],

    'API\\InventoryController' => [
        'business_summary' => 'Inventory Check API — provides real-time inventory availability checks for the mobile app. Vendors can scan QR codes to check serial number status and availability before installation.',
        'data_flow' => 'GET /api/inventory/check?serial=X → Lookup in inventory table → Return status (In Stock / Dispatched / Consumed) + item details',
        'depends_on' => ['Inventory', 'InventroyStreetLightModel', 'InventoryDispatch'],
        'business_domain' => 'Mobile API',
    ],

    'API\\ProjectController' => [
        'business_summary' => 'Project Data API — provides project information for the mobile app. Returns project details, assigned staff, and project configuration needed by the mobile app.',
        'data_flow' => 'GET /api/projects → Return user\'s assigned projects → GET /api/projects/{id} → Return project details with staff assignments',
        'depends_on' => ['Project', 'User'],
        'business_domain' => 'Mobile API',
    ],

    'API\\SiteController' => [
        'business_summary' => 'Site Data API — provides site/panchayat data for the mobile app. Returns site listings with ward breakdowns and pole target counts for field navigation.',
        'data_flow' => 'GET /api/sites?project_id=X → Return sites with ward structure → GET /api/sites/{id} → Return site details with pole data',
        'depends_on' => ['Site', 'Streetlight', 'Pole', 'Project'],
        'business_domain' => 'Mobile API',
    ],

    // ─── MODELS ──────────────────────────────────────────────

    'User' => [
        'business_summary' => 'Core user model representing all system users — Admin, Project Managers, Site Engineers, and Vendors. Each user has a role, is assigned to projects, and may manage other staff. Contains profile info, authentication credentials, and role-based relationships.',
        'data_flow' => 'Created by Admin → Assigned to Project → Assigned tasks/inventory → Performance tracked → Profile managed',
        'depends_on' => ['Project', 'Task', 'StreetlightTask', 'Pole', 'InventoryDispatch', 'Conveyance'],
        'business_domain' => 'Core Domain',
    ],

    'Project' => [
        'business_summary' => 'Represents a solar energy installation project (government contract). Each project has a type (1=Streetlight, 2=Rooftop), assigned staff, sites/panchayats, inventory stores, and targets. The project is the top-level organizational entity.',
        'data_flow' => 'Admin creates → Assign staff → Import sites → Create targets → Track inventory → Monitor progress → Generate reports',
        'depends_on' => ['User', 'Site', 'Streetlight', 'Stores', 'Task', 'StreetlightTask'],
        'business_domain' => 'Core Domain',
    ],

    'Pole' => [
        'business_summary' => 'Individual streetlight pole record. Contains GPS coordinates (lat/lng), survey photos (before image, after image, serial photos), installation status, equipment serial numbers (panel, luminary, battery), SIM number, and RMS push status. Each pole belongs to a task and site/panchayat.',
        'data_flow' => 'Field survey → Create record with GPS + photos → Link serial numbers from inventory → Mark installed → Push to RMS → Track in reports',
        'depends_on' => ['StreetlightTask', 'Streetlight', 'InventoryDispatch'],
        'business_domain' => 'Field Operations',
    ],

    'Streetlight' => [
        'business_summary' => 'Represents a panchayat/ward as a streetlight installation site. Contains district, block, panchayat, ward, and target pole count. Tracks number of surveyed and installed poles against the target.',
        'data_flow' => 'Imported from Excel → Assigned to task → Poles created under it → Progress tracked (surveyed/installed vs target)',
        'depends_on' => ['Project', 'StreetlightTask', 'Pole'],
        'business_domain' => 'Site Management',
    ],

    'StreetlightTask' => [
        'business_summary' => 'Work assignment linking a panchayat/site to field staff (engineer + vendor + PM). Contains assignment dates, status (Pending/In Progress/Completed), and relationship to poles. This is how field work is assigned and tracked.',
        'data_flow' => 'Admin assigns site → Create task record → Field app fetches tasks → Survey + Install poles → Update status → Complete when all poles done',
        'depends_on' => ['Streetlight', 'User', 'Pole', 'Project'],
        'business_domain' => 'Field Operations',
    ],

    'Task' => [
        'business_summary' => 'Work assignment for rooftop solar projects. Similar concept to StreetlightTask but for rooftop installations. Contains site assignment, staff assignment, status tracking, and materials consumed.',
        'data_flow' => 'Admin assigns site to engineer + vendor → Create task → Field work → Update status → Mark complete',
        'depends_on' => ['Site', 'User', 'Project'],
        'business_domain' => 'Field Operations',
    ],

    'Inventory' => [
        'business_summary' => 'Inventory item record for rooftop projects. Tracks product name, brand, serial number, quantity, unit, and received date. Quantity is decremented when dispatched to vendors.',
        'data_flow' => 'GRN import / Manual add → Stored in warehouse → Dispatched to vendor → Quantity decremented → Consumed at site',
        'depends_on' => ['Stores', 'Project'],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'InventroyStreetLightModel' => [
        'business_summary' => 'Streetlight inventory item. Stores solar equipment with item codes: SL01 (Panel), SL02 (Luminary with SIM), SL03 (Battery), SL04 (Structure). Tracks serial number, make, model, rate, and quantity. SL02 items carry SIM numbers for remote monitoring.',
        'data_flow' => 'GRN Excel import → Validate serial/SIM uniqueness → Store with quantity=1 per serial → Dispatch to vendor (quantity→0) → Consume at pole (link to pole record)',
        'depends_on' => ['Stores', 'Project', 'InventoryDispatch'],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'InventoryDispatch' => [
        'business_summary' => 'Tracks the dispatch of an inventory item from a store to a vendor. Links serial number to vendor, records dispatch date and value. When the vendor installs the item on a pole, the streetlight_pole_id is set and is_consumed is marked true.',
        'data_flow' => 'Create on dispatch → Link to vendor + store + project → Vendor installs → Link to pole (streetlight_pole_id) → Mark consumed → Can be returned if defective',
        'depends_on' => ['User', 'Stores', 'Project', 'Pole'],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'Stores' => [
        'business_summary' => 'Physical warehouse/store location associated with a project. Each store has a name, address, and an assigned store incharge (responsible person). All inventory for a project is tracked under its store.',
        'data_flow' => 'Admin creates store → Assign incharge → Import inventory to store → Track stock levels → Dispatch from store',
        'depends_on' => ['Project', 'User'],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'Site' => [
        'business_summary' => 'Physical project site for rooftop projects. Contains location details (state, city, pincode), site type, and associated poles. Represents where rooftop solar installations happen.',
        'data_flow' => 'Import from Excel / Manual create → Assign to task → Field work at site → Poles installed → Mark complete',
        'depends_on' => ['Project', 'Pole', 'Task'],
        'business_domain' => 'Site Management',
    ],

    'Meet' => [
        'business_summary' => 'Formal meeting record with type (Internal/Client/Government), date, location, and attendee tracking. Contains meeting notes and links to discussion points and follow-ups.',
        'data_flow' => 'Create meeting → Add attendees → Record discussion points → Track point status → Schedule follow-ups → Export minutes as PDF/Excel',
        'depends_on' => ['User', 'DiscussionPoint', 'FollowUp', 'Project'],
        'business_domain' => 'Meetings & Collaboration',
    ],

    'DiscussionPoint' => [
        'business_summary' => 'Individual agenda/discussion point within a meeting. Has status lifecycle (Open → In Progress → Resolved), priority, and assigned owner. Supports updates/comments over time.',
        'data_flow' => 'Created in meeting → Status tracked → Updates added → Resolved or carried to follow-up meeting',
        'depends_on' => ['Meet', 'User', 'DiscussionPointUpdates'],
        'business_domain' => 'Meetings & Collaboration',
    ],

    'Conveyance' => [
        'business_summary' => 'Daily travel conveyance claim submitted by field staff. Contains journey details, vehicle used, distance, fare amount, and approval status (Pending/Accepted/Rejected).',
        'data_flow' => 'Field staff submits from app → Admin reviews → Accept/Reject → Aggregate in TADA report → Disburse allowance',
        'depends_on' => ['User', 'Vehicle', 'Tada'],
        'business_domain' => 'Finance & Expense',
    ],

    'Tada' => [
        'business_summary' => 'Travel Allowance / Dearness Allowance (TA/DA) summary record. Aggregates daily conveyance claims into periodic summaries for disbursement. Contains total amount, distance, and approval status.',
        'data_flow' => 'Daily conveyances aggregated → Create TADA summary → Admin approval → Disbursement → Financial reporting',
        'depends_on' => ['User', 'Conveyance', 'Project'],
        'business_domain' => 'Finance & Expense',
    ],

    'Vehicle' => [
        'business_summary' => 'Vehicle type master data for travel allowance calculation. Contains vehicle name (Bike, Car, Bus, Auto, etc.) and per-kilometer fare rate used to calculate conveyance amounts.',
        'data_flow' => 'Admin defines vehicle types with rates → Staff selects vehicle during travel → Rate used to calculate fare',
        'depends_on' => [],
        'business_domain' => 'Finance & Expense',
    ],

    'ActivityLog' => [
        'business_summary' => 'Audit trail record for system actions. Captures who did what, when, and to which entity. Used for accountability and compliance tracking.',
        'data_flow' => 'System action triggers → ActivityLogger creates record → Admin views audit trail → Filter by entity/user/date',
        'depends_on' => ['User'],
        'business_domain' => 'Audit & Compliance',
    ],

    'RmsPushLog' => [
        'business_summary' => 'Log entry for each attempt to push pole data to the government RMS system. Records request payload, response, status, and error details for debugging integration issues.',
        'data_flow' => 'Push attempted → Log request/response → Track success/failure → Admin reviews push history',
        'depends_on' => ['Pole'],
        'business_domain' => 'Government Integration',
    ],

    'Candidate' => [
        'business_summary' => 'Job candidate record for recruitment. Contains personal details, contact info, resume, interview status, and hiring decision.',
        'data_flow' => 'Register candidate → Upload documents → Schedule interview → Update status → Hire or reject',
        'depends_on' => [],
        'business_domain' => 'HR & Recruitment',
    ],

    // ─── SERVICES ──────────────────────────────────────────────

    'DashboardAnalyticsService' => [
        'business_summary' => 'Analytics engine for the main dashboard. Computes district-wise performance metrics, top performer leaderboards, meeting summaries, and TA/DA financial overviews. Designed for efficiency with aggregate DB queries instead of loading full datasets.',
        'data_flow' => 'Called by HomeController → Accepts user + filters → Runs optimized aggregate queries → Returns structured analytics data',
        'depends_on' => ['User', 'Project', 'Pole', 'Streetlight', 'StreetlightTask', 'Meet', 'Tada'],
        'business_domain' => 'Dashboard & Reporting',
    ],

    'MeetingManagementService' => [
        'business_summary' => 'Service layer for meeting operations. Handles meeting CRUD, discussion point management, attendee management, follow-up scheduling, and notification dispatch.',
        'data_flow' => 'Controller delegates → Service validates business rules → Model operations → Notification dispatch',
        'depends_on' => ['Meet', 'DiscussionPoint', 'FollowUp', 'User'],
        'business_domain' => 'Meetings & Collaboration',
    ],

    'InventoryService' => [
        'business_summary' => 'Core inventory operations service. Handles adding inventory items with proper model selection (rooftop vs streetlight), validation, and database operations.',
        'data_flow' => 'Controller delegates → Service selects model type → Validates uniqueness → Creates inventory record',
        'depends_on' => ['Inventory', 'InventroyStreetLightModel'],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'InventoryHistoryService' => [
        'business_summary' => 'Tracks inventory movement history. Records when items are dispatched, consumed, returned, or replaced, creating a complete audit trail of inventory lifecycle events.',
        'data_flow' => 'Dispatch → Log "dispatched" event → Consume → Log "consumed" event → Return → Log "returned" event',
        'depends_on' => ['InventoryHistory', 'InventoryDispatch'],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'SiteManagementService' => [
        'business_summary' => 'Service layer for site/panchayat operations. Handles site creation, pole management, and site-level statistics aggregation.',
        'data_flow' => 'Controller delegates → Service handles site CRUD → Aggregates pole statistics → Returns structured data',
        'depends_on' => ['Site', 'Streetlight', 'Pole'],
        'business_domain' => 'Site Management',
    ],

    'PerformanceService' => [
        'business_summary' => 'Calculates staff performance metrics across projects. Computes survey/installation rates, completion percentages, and ranking scores for leaderboards.',
        'data_flow' => 'Request with user + project + date range → Query poles/tasks → Calculate metrics → Return ranked results',
        'depends_on' => ['User', 'Pole', 'Task', 'StreetlightTask'],
        'business_domain' => 'Dashboard & Reporting',
    ],

    'PoleImportService' => [
        'business_summary' => 'Handles bulk pole import from Excel files. Processes large datasets in chunks for memory efficiency, validates GPS coordinates and serial numbers, and creates pole records.',
        'data_flow' => 'Excel upload → Parse in chunks → Validate coordinates + serials → Create Pole records → Report success/errors',
        'depends_on' => ['Pole', 'Streetlight', 'ProcessPoleImportChunk'],
        'business_domain' => 'Field Operations',
    ],

    'ActivityLogger' => [
        'business_summary' => 'Centralized logging service for audit trail. Called by controllers and services to record significant actions (inventory imports, dispatches, task changes, etc.) with context data.',
        'data_flow' => 'Action occurs → Logger called with entity type + action + metadata → ActivityLog record created → Available in audit view',
        'depends_on' => ['ActivityLog'],
        'business_domain' => 'Audit & Compliance',
    ],

    'TargetDeletionService' => [
        'business_summary' => 'Handles bulk target (task) deletion with associated data cleanup. For streetlight projects, this includes deleting poles, returning consumed inventory to dispatched state, updating site counts, and processing in chunks to avoid timeouts.',
        'data_flow' => 'Select targets → Dispatch async job → Process chunks → Delete poles + dispatch records → Update site counts → Report progress',
        'depends_on' => ['StreetlightTask', 'Pole', 'InventoryDispatch', 'Streetlight'],
        'business_domain' => 'Field Operations',
    ],

    'ConveyanceService' => [
        'business_summary' => 'Service layer for conveyance/travel claim operations. Handles business rules for fare calculation, validation, and status management.',
        'data_flow' => 'Controller delegates → Service applies business rules → Model operations → Return result',
        'depends_on' => ['Conveyance', 'Vehicle', 'User'],
        'business_domain' => 'Finance & Expense',
    ],

    'DataTransformationService' => [
        'business_summary' => 'Transforms data between different formats for backup and restore operations. Handles serialization, format conversion, and data migration tasks.',
        'data_flow' => 'Source data → Transform to target format → Validate → Output (backup file or database)',
        'depends_on' => [],
        'business_domain' => 'System Administration',
    ],

    // ─── HELPERS ──────────────────────────────────────────────

    'ExcelHelper' => [
        'business_summary' => 'Utility for Excel file generation across the application. Provides streaming multi-sheet export capability used by dashboard exports, inventory exports, and task exports. Handles memory-efficient writing for large datasets.',
        'data_flow' => 'Controller calls with data → ExcelHelper creates workbook → Write headers + rows → Stream download to browser',
        'depends_on' => [],
        'business_domain' => 'Utility',
    ],

    'PrintHelper' => [
        'business_summary' => 'PDF generation helper for printing reports and documents. Used for generating printable versions of JICR reports, meeting minutes, and other formal documents.',
        'data_flow' => 'Data prepared → PrintHelper formats → Generate PDF → Download or display',
        'depends_on' => [],
        'business_domain' => 'Utility',
    ],

    'WhatsappHelper' => [
        'business_summary' => 'WhatsApp messaging integration helper. Used for sending OTPs during mobile number changes, meeting notifications to attendees, and other automated messages via WhatsApp Business API.',
        'data_flow' => 'Caller prepares message → WhatsappHelper formats payload → POST to WhatsApp API → Return delivery status',
        'depends_on' => [],
        'business_domain' => 'Utility',
    ],

    'RemoteApiHelper' => [
        'business_summary' => 'HTTP client helper for external API communication. Primarily used for pushing pole data to the government RMS system. Handles request/response formatting, error handling, and retry logic.',
        'data_flow' => 'Prepare payload → RemoteApiHelper sends HTTP request → Parse response → Return structured result',
        'depends_on' => [],
        'business_domain' => 'Utility',
    ],

    // ─── IMPORTS / EXPORTS ──────────────────────────────────────

    'InventoryImport' => [
        'business_summary' => 'Excel importer for rooftop project inventory. Parses GRN (Goods Received Note) Excel files to bulk-add inventory items to a store.',
        'data_flow' => 'Excel upload → Parse rows → Validate item codes + serials → Create Inventory records with project_id + store_id',
        'depends_on' => ['Inventory'],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'InventroyStreetLight' => [
        'business_summary' => 'Excel importer for streetlight inventory GRN. Parses item code, item name, serial number, SIM number (for SL02), make, model, and rate. Validates serial/SIM uniqueness and reports errors for duplicate entries.',
        'data_flow' => 'Excel upload → Parse rows → Check serial/SIM uniqueness → Create InventroyStreetLightModel records → Report imported count + errors',
        'depends_on' => ['InventroyStreetLightModel'],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'InventoryDispatchImport' => [
        'business_summary' => 'Excel importer for bulk inventory dispatch. Allows admins to dispatch multiple items to a vendor at once by uploading an Excel file with serial numbers.',
        'data_flow' => 'Excel upload → Parse serial numbers → Validate availability → Create InventoryDispatch records → Decrement quantities',
        'depends_on' => ['InventoryDispatch', 'InventroyStreetLightModel'],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'SiteImport' => [
        'business_summary' => 'Excel importer for rooftop project sites. Parses site details (name, address, state, city, pincode) from Excel and creates Site records.',
        'data_flow' => 'Excel upload → Parse rows → Validate → Create Site records under project',
        'depends_on' => ['Site'],
        'business_domain' => 'Site Management',
    ],

    'StreetlightImport' => [
        'business_summary' => 'Excel importer for streetlight panchayat sites. Parses district, block, panchayat, ward, and target pole count. Creates Streetlight records under the project.',
        'data_flow' => 'Excel upload → Parse district/panchayat/ward → Create Streetlight records → Set target pole count',
        'depends_on' => ['Streetlight'],
        'business_domain' => 'Site Management',
    ],

    'StreetlightPoleImport' => [
        'business_summary' => 'Excel importer for pre-surveyed pole data. Allows importing poles with pre-filled GPS coordinates and details, useful when survey data comes from external sources.',
        'data_flow' => 'Excel upload → Parse pole details with coordinates → Create Pole records → Link to site',
        'depends_on' => ['Pole', 'Streetlight'],
        'business_domain' => 'Field Operations',
    ],

    'SitePoleImport' => [
        'business_summary' => 'Excel importer for poles under a specific site. Imports pole data (coordinates, ward, status) directly under a site for bulk data entry.',
        'data_flow' => 'Excel upload → Parse pole rows → Validate → Create Pole records under site',
        'depends_on' => ['Pole', 'Site'],
        'business_domain' => 'Site Management',
    ],

    'StaffImport' => [
        'business_summary' => 'Excel importer for bulk staff creation. Parses employee details (name, role, mobile, email, project assignment) from Excel to onboard multiple staff members at once.',
        'data_flow' => 'Excel upload → Parse staff rows → Validate uniqueness → Create User records → Assign roles + projects',
        'depends_on' => ['User'],
        'business_domain' => 'Staff & HR',
    ],

    'TargetImport' => [
        'business_summary' => 'Excel importer for bulk target/task creation. Parses site-to-staff assignments from Excel to create multiple tasks at once.',
        'data_flow' => 'Excel upload → Parse assignments → Create StreetlightTask or Task records → Assign staff',
        'depends_on' => ['StreetlightTask', 'Task'],
        'business_domain' => 'Field Operations',
    ],

    'VendorImport' => [
        'business_summary' => 'Excel importer for bulk vendor creation. Similar to StaffImport but specifically for vendor accounts with vendor-specific fields.',
        'data_flow' => 'Excel upload → Parse vendor rows → Create User records with VENDOR role',
        'depends_on' => ['User'],
        'business_domain' => 'Vendor Management',
    ],

    'CandidatesImport' => [
        'business_summary' => 'Excel importer for bulk candidate registration during recruitment drives.',
        'data_flow' => 'Excel upload → Parse candidate rows → Create Candidate records',
        'depends_on' => ['Candidate'],
        'business_domain' => 'HR & Recruitment',
    ],

    'InventoryExport' => [
        'business_summary' => 'Excel exporter for inventory data with dispatch status. Exports item code, serial number, SIM, availability status, vendor name, and dates. Uses streaming for large datasets.',
        'data_flow' => 'Query builder with filters → Stream rows to Excel → Download file',
        'depends_on' => ['Inventory', 'InventroyStreetLightModel', 'InventoryDispatch'],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'TasksExport' => [
        'business_summary' => 'Excel exporter for task/target data. Exports task assignments with site details, staff names, status, and completion dates.',
        'data_flow' => 'Query builder with project filter → Stream rows to Excel → Download file',
        'depends_on' => ['Task', 'StreetlightTask'],
        'business_domain' => 'Field Operations',
    ],

    // ─── ENUMS ──────────────────────────────────────────────

    'UserRole' => [
        'business_summary' => 'Defines the four user roles in the system: ADMIN (full access), PROJECT_MANAGER (manages a project\'s staff and operations), SITE_ENGINEER (performs field surveys and monitors installation), and VENDOR (performs physical equipment installation).',
        'data_flow' => 'Used throughout to check permissions → Determines dashboard view → Controls data access scope',
        'depends_on' => [],
        'business_domain' => 'Core Domain',
    ],

    'TaskStatus' => [
        'business_summary' => 'Defines task lifecycle states: Pending (newly created), In Progress (field work started), Completed (all work done), and Rejected (failed quality check). Controls allowed transitions and reporting.',
        'data_flow' => 'Task created as Pending → Field work starts (In Progress) → Work verified (Completed or Rejected)',
        'depends_on' => [],
        'business_domain' => 'Field Operations',
    ],

    // ─── PROVIDERS ──────────────────────────────────────────────

    'RepositoryServiceProvider' => [
        'business_summary' => 'Dependency injection configuration that binds all interfaces to their concrete implementations. This is the architectural backbone that enables the Repository-Service pattern, allowing controllers to depend on interfaces rather than concrete classes.',
        'data_flow' => 'Boot: Registers interface → implementation bindings → Laravel IoC container resolves dependencies at runtime',
        'depends_on' => [],
        'business_domain' => 'Architecture',
    ],

    // ─── JOBS ──────────────────────────────────────────────

    'ProcessPoleImportChunk' => [
        'business_summary' => 'Background job that processes a chunk of pole import data. Part of the chunked import system that handles large Excel files without hitting memory limits or timeout errors.',
        'data_flow' => 'PoleImportService dispatches chunks → Queue worker picks up → Process rows → Create Pole records → Update progress',
        'depends_on' => ['Pole', 'Streetlight'],
        'business_domain' => 'System Administration',
    ],

    'ProcessTargetDeletionChunk' => [
        'business_summary' => 'Background job that processes a chunk of target deletions. Handles cascade cleanup: deletes poles, returns consumed inventory, updates site counts, and removes task records.',
        'data_flow' => 'ProjectsController dispatches chunks → Queue worker picks up → Delete poles + inventory → Update site counts → Report progress',
        'depends_on' => ['StreetlightTask', 'Pole', 'InventoryDispatch', 'Streetlight'],
        'business_domain' => 'System Administration',
    ],

    // ─── AUTH CONTROLLERS (remaining) ──────────────────────────

    'ConfirmPasswordController' => [
        'business_summary' => 'Password Confirmation — handles the password re-confirmation flow required before sensitive operations like account deletion or role changes. Ensures the current session user re-enters their password for security.',
        'data_flow' => 'Sensitive action triggers password confirm → User enters password → Verify against stored hash → Allow or deny the action',
        'depends_on' => ['User'],
        'business_domain' => 'Authentication',
    ],

    'ForgotPasswordController' => [
        'business_summary' => 'Password Reset Request — handles the "Forgot Password" flow. Users enter their email to receive a password reset link. Sends a reset token via email using Laravel\'s built-in password broker.',
        'data_flow' => 'User clicks "Forgot Password" → Enter email → Validate existence → Send reset email with token → Token stored in password_resets table',
        'depends_on' => ['User'],
        'business_domain' => 'Authentication',
    ],

    'ResetPasswordController' => [
        'business_summary' => 'Password Reset Execution — handles the actual password reset after the user clicks the link from the forgot-password email. Validates the reset token, accepts a new password, and updates the user record.',
        'data_flow' => 'User clicks reset link → Validate token → Enter new password → Hash and update User record → Redirect to login',
        'depends_on' => ['User'],
        'business_domain' => 'Authentication',
    ],

    'VerificationController' => [
        'business_summary' => 'Email Verification — handles email address verification flow. After registration, sends a verification email with a signed URL. Verifies the token on click.',
        'data_flow' => 'User registers → Verification email sent → User clicks link → Token verified → email_verified_at set → Access granted',
        'depends_on' => ['User'],
        'business_domain' => 'Authentication',
    ],

    // ─── MIDDLEWARE ──────────────────────────────────────────────

    'Authenticate' => [
        'business_summary' => 'Authentication guard middleware. Checks if the current request has a valid authenticated session. Unauthenticated users are redirected to the login page. Applied to all routes requiring login.',
        'data_flow' => 'HTTP Request → Check session/token → Authenticated: proceed → Unauthenticated: redirect to /login',
        'depends_on' => ['User'],
        'business_domain' => 'Security',
    ],

    'EncryptCookies' => [
        'business_summary' => 'Cookie encryption middleware. Automatically encrypts all outgoing cookies and decrypts incoming cookies to prevent tampering and ensure data privacy. Part of Laravel\'s default security stack.',
        'data_flow' => 'Response cookies → Encrypt with APP_KEY → Send to browser → Next request: Decrypt incoming cookies → Pass to application',
        'depends_on' => [],
        'business_domain' => 'Security',
    ],

    'PreventRequestsDuringMaintenance' => [
        'business_summary' => 'Maintenance mode gate. When the application is put into maintenance mode (php artisan down), this middleware returns a 503 Service Unavailable response to all incoming requests, except whitelisted IPs or paths.',
        'data_flow' => 'HTTP Request → Check maintenance mode flag → Active: return 503 page → Inactive: proceed normally',
        'depends_on' => [],
        'business_domain' => 'System Administration',
    ],

    'RedirectIfAuthenticated' => [
        'business_summary' => 'Guest-only route guard. Redirects already-authenticated users away from guest-only pages (login, register) to the dashboard. Prevents logged-in users from seeing the login form again.',
        'data_flow' => 'HTTP Request to guest route → Check auth status → Authenticated: redirect to /home → Not authenticated: proceed',
        'depends_on' => ['User'],
        'business_domain' => 'Security',
    ],

    'RestrictToMeetings' => [
        'business_summary' => 'Meeting access control middleware. Restricts access to meeting-related routes to only users who are authorized to view or manage meetings (typically admins and project managers). Custom business middleware.',
        'data_flow' => 'HTTP Request to meeting route → Check user role/permissions → Authorized: proceed → Unauthorized: abort 403',
        'depends_on' => ['User'],
        'business_domain' => 'Security',
    ],

    'RoleMiddleware' => [
        'business_summary' => 'Role-based access control (RBAC) middleware. Checks if the authenticated user has the required role(s) to access a route. Used across the application to enforce role hierarchy: Admin > PM > Engineer > Vendor.',
        'data_flow' => 'HTTP Request → Extract required role from route → Check user role → Match: proceed → No match: abort 403 Forbidden',
        'depends_on' => ['User', 'UserRole'],
        'business_domain' => 'Security',
    ],

    'TrimStrings' => [
        'business_summary' => 'Input sanitization middleware. Automatically trims leading and trailing whitespace from all incoming request string values. Prevents data quality issues from accidental spaces in form inputs.',
        'data_flow' => 'HTTP Request → Iterate all string inputs → Trim whitespace → Pass cleaned data to controller',
        'depends_on' => [],
        'business_domain' => 'Data Validation',
    ],

    'TrustHosts' => [
        'business_summary' => 'Host validation middleware. Restricts which hostnames the application trusts for incoming requests. Prevents HTTP Host header attacks by validating the Host header against a whitelist.',
        'data_flow' => 'HTTP Request → Validate Host header → Trusted: proceed → Untrusted: reject request',
        'depends_on' => [],
        'business_domain' => 'Security',
    ],

    'TrustProxies' => [
        'business_summary' => 'Reverse proxy configuration middleware. Configures which proxy headers to trust (X-Forwarded-For, X-Forwarded-Proto, etc.) when the application sits behind a load balancer or CDN like AWS ALB or CloudFront.',
        'data_flow' => 'HTTP Request via proxy → Read forwarded headers → Set trusted proxy IPs → Correct request URL/protocol detection',
        'depends_on' => [],
        'business_domain' => 'Infrastructure',
    ],

    'ValidateSignature' => [
        'business_summary' => 'URL signature validation middleware. Validates that signed URLs (used for email verification, temporary download links) have not been tampered with and have not expired.',
        'data_flow' => 'Signed URL request → Validate HMAC signature → Check expiration → Valid: proceed → Invalid: abort 403',
        'depends_on' => [],
        'business_domain' => 'Security',
    ],

    'VerifyCsrfToken' => [
        'business_summary' => 'CSRF protection middleware. Validates that POST/PUT/DELETE requests include a valid CSRF token, preventing cross-site request forgery attacks. API routes are typically excluded.',
        'data_flow' => 'Form submission → Check _token field or X-CSRF-TOKEN header → Match session token → Valid: proceed → Invalid: abort 419',
        'depends_on' => [],
        'business_domain' => 'Security',
    ],

    // ─── FORM REQUESTS ──────────────────────────────────────────

    'StoreProjectRequest' => [
        'business_summary' => 'Validates input for creating a new project. Enforces required fields: project name, type (streetlight/rooftop), districts, and start date. Prevents creation of projects without essential configuration.',
        'data_flow' => 'POST /projects → StoreProjectRequest validates → Pass: controller creates project → Fail: redirect back with errors',
        'depends_on' => ['Project'],
        'business_domain' => 'Project Management',
    ],

    'UpdateProjectRequest' => [
        'business_summary' => 'Validates input for updating an existing project. Similar rules to StoreProjectRequest but allows partial updates and validates against the existing project record to prevent conflicts.',
        'data_flow' => 'PUT /projects/{id} → UpdateProjectRequest validates → Pass: controller updates project → Fail: redirect back with errors',
        'depends_on' => ['Project'],
        'business_domain' => 'Project Management',
    ],

    'AssignTaskRequest' => [
        'business_summary' => 'Validates input for assigning field tasks (targets) to staff. Enforces required fields: site selection, engineer assignment, vendor assignment, and date range. Ensures business rules like date ordering are met.',
        'data_flow' => 'POST /tasks/assign → AssignTaskRequest validates → Check site + staff existence → Pass: create task → Fail: redirect with errors',
        'depends_on' => ['StreetlightTask', 'Task', 'User'],
        'business_domain' => 'Field Operations',
    ],

    'StoreTaskRequest' => [
        'business_summary' => 'Validates input for creating a new task/target. Checks that selected sites exist, assigned staff are valid, and date ranges are logical (end date >= start date). Prevents orphaned task records.',
        'data_flow' => 'POST /tasks → StoreTaskRequest validates → Check relationships → Pass: controller creates task → Fail: redirect with errors',
        'depends_on' => ['Task', 'StreetlightTask', 'User', 'Streetlight'],
        'business_domain' => 'Field Operations',
    ],

    'UpdateTaskRequest' => [
        'business_summary' => 'Validates input for updating an existing task/target. Enforces business rule: completed tasks with installed poles cannot be reassigned to different staff (protects historical data integrity).',
        'data_flow' => 'PUT /tasks/{id} → UpdateTaskRequest validates → Check completion status → Pass: controller updates → Fail: redirect with errors',
        'depends_on' => ['Task', 'StreetlightTask', 'User'],
        'business_domain' => 'Field Operations',
    ],

    // ─── MODELS (remaining) ──────────────────────────────────────

    'City' => [
        'business_summary' => 'City master data for TA/DA calculations. Each city has a category (A/B/C tier) that determines daily allowance rates for field staff. Linked to a state for geographic hierarchy.',
        'data_flow' => 'Admin defines cities with categories → Staff selects city during travel → Category determines daily allowance rate',
        'depends_on' => ['State'],
        'business_domain' => 'Finance & Expense',
    ],

    'DiscussionPointUpdates' => [
        'business_summary' => 'Track status changes and comments on meeting discussion points over time. Each update records who changed what, when, and any notes — providing a full change history for each agenda item.',
        'data_flow' => 'Discussion point status changed → Create update record → Display in timeline under discussion point → Available for audit',
        'depends_on' => ['DiscussionPoint', 'User'],
        'business_domain' => 'Meetings & Collaboration',
    ],

    'DistrictCode' => [
        'business_summary' => 'District code master data for geographic hierarchy. Maps district names to standardized codes used in pole numbering format (e.g., first 3 letters of district name).',
        'data_flow' => 'Loaded during pole number generation → Provides standardized district prefix → Used in complete_pole_number format',
        'depends_on' => [],
        'business_domain' => 'Core Domain',
    ],

    'FollowUp' => [
        'business_summary' => 'Meeting follow-up record. Links to a discussion point from a previous meeting that needs continued attention. Tracks follow-up date, assigned person, and completion status.',
        'data_flow' => 'Discussion point unresolved → Create follow-up → Assign to next meeting → Track until resolved',
        'depends_on' => ['Meet', 'DiscussionPoint'],
        'business_domain' => 'Meetings & Collaboration',
    ],

    'HotelExpense' => [
        'business_summary' => 'Hotel/accommodation expense record for field staff travel. Captures hotel name, check-in/check-out dates, amount, and receipt. Part of the TA/DA reimbursement system.',
        'data_flow' => 'Staff submits hotel bill → Record created → Admin reviews → Approve/Reject → Include in TA/DA summary',
        'depends_on' => ['User', 'Tada'],
        'business_domain' => 'Finance & Expense',
    ],

    'InventoryHistory' => [
        'business_summary' => 'Audit trail for inventory lifecycle events. Records each inventory operation (created, dispatched, consumed, returned, replaced, locked, unlocked) with before/after quantities and metadata.',
        'data_flow' => 'Inventory action occurs → InventoryHistoryService logs event → Record stored → Admin reviews audit trail',
        'depends_on' => ['Inventory', 'InventoryDispatch', 'User'],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'Journey' => [
        'business_summary' => 'Individual travel journey leg within a conveyance claim. Contains departure, destination, distance, vehicle type, and calculated fare. Multiple journeys make up a single day\'s travel claim.',
        'data_flow' => 'Staff records journey → Fare calculated (distance × vehicle rate) → Multiple journeys aggregated into daily conveyance → Admin reviews',
        'depends_on' => ['Conveyance', 'Vehicle'],
        'business_domain' => 'Finance & Expense',
    ],

    'MeetingNoteHistory' => [
        'business_summary' => 'Version history for meeting notes. Tracks changes to meeting notes over time, allowing stakeholders to see how notes were edited and by whom.',
        'data_flow' => 'Meeting notes edited → Previous version stored → Change history available → Audit trail maintained',
        'depends_on' => ['Meet', 'User'],
        'business_domain' => 'Meetings & Collaboration',
    ],

    'Permission' => [
        'business_summary' => 'Permission model for fine-grained access control. Defines specific permissions (e.g., can_export_inventory, can_delete_poles) that can be assigned to roles for granular authorization beyond the basic RBAC.',
        'data_flow' => 'Admin defines permissions → Assign to roles → Middleware checks permission → Grant or deny access',
        'depends_on' => ['Role'],
        'business_domain' => 'Security',
    ],

    'PoleImportJob' => [
        'business_summary' => 'Job tracking model for pole import operations. Records the status and progress of bulk pole import jobs, including total rows, processed rows, and error count.',
        'data_flow' => 'Import started → Job record created → Chunks processed → Progress updated → Completion recorded',
        'depends_on' => ['ProcessPoleImportChunk'],
        'business_domain' => 'System Administration',
    ],

    'Role' => [
        'business_summary' => 'Role model for role-based access control. Defines system roles (Admin, Project Manager, Site Engineer, Vendor) with associated permissions. Each user belongs to one role.',
        'data_flow' => 'Admin defines role → Assign permissions → Assign to users → Middleware checks role for route access',
        'depends_on' => ['Permission'],
        'business_domain' => 'Security',
    ],

    'State' => [
        'business_summary' => 'State/province master data for geographic hierarchy. Used in site addresses, city categorization, and dropdown population in forms. India-specific state list.',
        'data_flow' => 'Pre-populated master data → Used in dropdowns → Links to City → Used in site/panchayat addresses',
        'depends_on' => [],
        'business_domain' => 'Core Domain',
    ],

    'TargetDeletionJob' => [
        'business_summary' => 'Job tracking model for bulk target deletion operations. Records deletion progress, total targets to delete, processed count, and error log. Supports progress polling from the UI.',
        'data_flow' => 'Deletion started → Job record created → Chunks processed → Progress polled by UI → Completion recorded',
        'depends_on' => ['ProcessTargetDeletionChunk'],
        'business_domain' => 'System Administration',
    ],

    'UserCategory' => [
        'business_summary' => 'Staff category for TA/DA rate determination. Maps users to expense categories (e.g., senior engineer, junior engineer) which determine their daily allowance and per-km travel rates.',
        'data_flow' => 'Admin assigns category to staff → Category determines allowance rates → Rates applied to conveyance calculations',
        'depends_on' => ['User'],
        'business_domain' => 'Finance & Expense',
    ],

    'Whiteboard' => [
        'business_summary' => 'Shared whiteboard/notes content model. Stores collaborative notes and action items created during project planning sessions. Supports rich text and tagging.',
        'data_flow' => 'Create whiteboard → Add content → Share with team → Edit collaboratively → Reference in meetings',
        'depends_on' => ['User', 'Project'],
        'business_domain' => 'Meetings & Collaboration',
    ],

    'dailyfare' => [
        'business_summary' => 'Daily fare rate master data for TA/DA. Defines the daily allowance (dearness allowance) amount based on staff category and city tier. Used to calculate the per-day DA component of travel claims.',
        'data_flow' => 'Admin sets rates per category + city tier → Staff submits claim → System looks up applicable rate → Calculates daily allowance',
        'depends_on' => ['UserCategory', 'City'],
        'business_domain' => 'Finance & Expense',
    ],

    'travelfare' => [
        'business_summary' => 'Travel fare rate master data for TA/DA. Defines per-kilometer rates for each vehicle type and staff category. Used to calculate the travel component of conveyance claims.',
        'data_flow' => 'Admin sets per-km rates → Staff selects vehicle + enters distance → System calculates: distance × rate → Travel allowance amount',
        'depends_on' => ['Vehicle', 'UserCategory'],
        'business_domain' => 'Finance & Expense',
    ],

    // ─── CONTRACTS / INTERFACES ──────────────────────────────────

    'RepositoryInterface' => [
        'business_summary' => 'Base interface for the Repository pattern. Defines standard CRUD methods (all, find, create, update, delete) that every repository must implement. Enables database abstraction.',
        'data_flow' => 'Controller → Service → Repository.method() → Eloquent query → Database',
        'depends_on' => [],
        'business_domain' => 'Architecture',
    ],

    'ServiceInterface' => [
        'business_summary' => 'Base interface for the Service layer pattern. Defines standard service methods that business logic services must implement. Services sit between controllers and repositories.',
        'data_flow' => 'Controller → Service.method() → Business logic → Repository → Database',
        'depends_on' => [],
        'business_domain' => 'Architecture',
    ],

    'AnalyticsServiceInterface' => [
        'business_summary' => 'Contract for analytics services. Defines methods for computing dashboard metrics, district-wise breakdowns, top performers, and aggregated statistics.',
        'data_flow' => 'HomeController → AnalyticsService (implements this) → Aggregated DB queries → Returns analytics data',
        'depends_on' => [],
        'business_domain' => 'Dashboard & Reporting',
    ],

    'DashboardServiceInterface' => [
        'business_summary' => 'Contract for dashboard data services. Defines methods for fetching and structuring dashboard widgets, KPIs, and summary data.',
        'data_flow' => 'HomeController → DashboardService (implements this) → Structured widget data → Dashboard view',
        'depends_on' => [],
        'business_domain' => 'Dashboard & Reporting',
    ],

    'InventoryStrategyInterface' => [
        'business_summary' => 'Strategy pattern interface for inventory operations. Different project types (streetlight vs rooftop) use different inventory models and logic. This interface ensures consistent API across strategies.',
        'data_flow' => 'InventoryController → Resolve strategy by project type → Execute through uniform interface → Model-specific logic runs',
        'depends_on' => [],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'InventoryServiceInterface' => [
        'business_summary' => 'Contract for inventory service operations. Defines methods for adding, dispatching, returning, and replacing inventory items with proper validation.',
        'data_flow' => 'InventoryController → InventoryService (implements this) → Validate + execute → Model operations → History logging',
        'depends_on' => [],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'MeetingRepositoryInterface' => [
        'business_summary' => 'Contract for meeting data access. Defines methods for querying meetings with filters, fetching meetings with discussion points, and aggregating meeting statistics.',
        'data_flow' => 'MeetingService → MeetingRepository (implements this) → Eloquent queries → Meet + DiscussionPoint data',
        'depends_on' => [],
        'business_domain' => 'Meetings & Collaboration',
    ],

    'MeetingServiceInterface' => [
        'business_summary' => 'Contract for meeting business logic. Defines methods for creating meetings, managing discussion points, scheduling follow-ups, and generating meeting exports.',
        'data_flow' => 'MeetController → MeetingService (implements this) → Business rules → Repository calls → Notifications',
        'depends_on' => [],
        'business_domain' => 'Meetings & Collaboration',
    ],

    'PerformanceServiceInterface' => [
        'business_summary' => 'Contract for performance calculation services. Defines methods for computing staff performance metrics, ranking, and leaderboard data.',
        'data_flow' => 'PerformanceController → PerformanceService (implements this) → Calculate metrics → Return ranked results',
        'depends_on' => [],
        'business_domain' => 'Dashboard & Reporting',
    ],

    'ProjectRepositoryInterface' => [
        'business_summary' => 'Contract for project data access. Defines methods for querying projects with related data (staff, sites, stores), filtering by user access, and aggregating project statistics.',
        'data_flow' => 'ProjectService → ProjectRepository (implements this) → Eloquent queries → Project data',
        'depends_on' => [],
        'business_domain' => 'Project Management',
    ],

    'ProjectServiceInterface' => [
        'business_summary' => 'Contract for project business logic. Defines methods for project CRUD, staff assignment, target management, and progress tracking.',
        'data_flow' => 'ProjectsController → ProjectService (implements this) → Business rules → Repository calls → Model operations',
        'depends_on' => [],
        'business_domain' => 'Project Management',
    ],

    'SiteRepositoryInterface' => [
        'business_summary' => 'Contract for site data access. Defines methods for querying sites with pole aggregates, filtering by project and district, and bulk operations.',
        'data_flow' => 'SiteService → SiteRepository (implements this) → Eloquent queries with joins → Site + Pole data',
        'depends_on' => [],
        'business_domain' => 'Site Management',
    ],

    'SiteServiceInterface' => [
        'business_summary' => 'Contract for site business logic. Defines methods for site management, pole association, target tracking, and import operations.',
        'data_flow' => 'SiteController → SiteService (implements this) → Business rules → Repository → Model operations',
        'depends_on' => [],
        'business_domain' => 'Site Management',
    ],

    'TaskRepositoryInterface' => [
        'business_summary' => 'Contract for task data access. Defines methods for querying tasks with staff and site relationships, filtering by status, and aggregating completion statistics.',
        'data_flow' => 'TaskService → TaskRepository (implements this) → Eloquent queries → Task data with relationships',
        'depends_on' => [],
        'business_domain' => 'Field Operations',
    ],

    'TaskServiceInterface' => [
        'business_summary' => 'Contract for task business logic. Defines methods for bulk task creation, assignment, status transitions, ward conflict checking, and export.',
        'data_flow' => 'TasksController → TaskService (implements this) → Business rules → Repository → Model operations',
        'depends_on' => [],
        'business_domain' => 'Field Operations',
    ],

    'TaskStateMachineInterface' => [
        'business_summary' => 'Contract for task state machine. Defines valid state transitions (Pending → In Progress → Completed/Rejected) and guards that prevent invalid transitions.',
        'data_flow' => 'Task status change requested → State machine validates transition → Allowed: update status → Denied: throw exception',
        'depends_on' => [],
        'business_domain' => 'Field Operations',
    ],

    'TaskTypeStrategyInterface' => [
        'business_summary' => 'Strategy pattern interface for different task types (Streetlight vs Rooftop). Each project type has different task creation, tracking, and reporting logic.',
        'data_flow' => 'TaskService → Resolve strategy by project type → Execute through uniform interface → Type-specific logic runs',
        'depends_on' => [],
        'business_domain' => 'Field Operations',
    ],

    'UserRepositoryInterface' => [
        'business_summary' => 'Contract for user data access. Defines methods for querying users by role, project assignment, and performance aggregates.',
        'data_flow' => 'UserService → UserRepository (implements this) → Eloquent queries → User data with roles and assignments',
        'depends_on' => [],
        'business_domain' => 'Staff & HR',
    ],

    'UserServiceInterface' => [
        'business_summary' => 'Contract for user business logic. Defines methods for user CRUD, role management, project assignment, and OTP verification.',
        'data_flow' => 'StaffController → UserService (implements this) → Business rules → Repository → User model operations',
        'depends_on' => [],
        'business_domain' => 'Staff & HR',
    ],

    // ─── EXCEL EXPORTS (remaining) ──────────────────────────────

    'InventoryImportFormatExport' => [
        'business_summary' => 'Generates a blank Excel template for inventory GRN import. Provides pre-formatted headers (Item Code, Serial Number, SIM Number, Make, Model, Rate) so users can fill in data correctly.',
        'data_flow' => 'User requests template → Generate Excel with headers + sample data + validation notes → Download',
        'depends_on' => [],
        'business_domain' => 'Inventory & Warehouse',
    ],

    'StreetlightPoleImportFormatExport' => [
        'business_summary' => 'Generates a blank Excel template for pole data import. Provides pre-formatted headers (District, Block, Panchayat, Ward, Pole Number, Latitude, Longitude) for bulk pole data entry.',
        'data_flow' => 'User requests template → Generate Excel with headers + format guidelines → Download → Fill → Upload back',
        'depends_on' => [],
        'business_domain' => 'Field Operations',
    ],

    // ─── ENUMS (remaining) ──────────────────────────────────────

    'InstallationPhase' => [
        'business_summary' => 'Defines the phases of streetlight pole installation: Survey (initial site visit with GPS capture), Installation (physical mounting of equipment), and Commissioning (power-on and RMS registration).',
        'data_flow' => 'Pole goes through phases: Survey → Installation → Commissioning → Each phase updates different fields on the Pole record',
        'depends_on' => [],
        'business_domain' => 'Field Operations',
    ],

    'ProjectType' => [
        'business_summary' => 'Defines the two types of solar energy projects: STREETLIGHT (1) for street/public lighting and ROOFTOP (2) for rooftop solar installations. Determines which models, forms, and logic paths are used throughout the system.',
        'data_flow' => 'Project created with type → Type determines: Model (Streetlight vs Site), Task model, Inventory model, Import format, Report templates',
        'depends_on' => [],
        'business_domain' => 'Core Domain',
    ],

    // ─── MAILABLES ──────────────────────────────────────────────

    'AppointmentLetter' => [
        'business_summary' => 'Appointment letter email for hired candidates. Generates a formal PDF appointment letter with joining date, designation, and salary details, then sends it as an email attachment to the candidate.',
        'data_flow' => 'Candidate hired → Generate appointment letter PDF → Attach to email → Send to candidate email address',
        'depends_on' => ['Candidate'],
        'business_domain' => 'HR & Recruitment',
    ],

    'CandidateMail' => [
        'business_summary' => 'General candidate communication email. Used to send interview invitations, status updates, and other HR communications to job candidates during the recruitment process.',
        'data_flow' => 'HR action triggers → Prepare email content → Send to candidate → Track delivery',
        'depends_on' => ['Candidate'],
        'business_domain' => 'HR & Recruitment',
    ],

    'RejectionLetter' => [
        'business_summary' => 'Rejection notification email for candidates who did not clear the hiring process. Sends a professional rejection message with feedback. Can be triggered individually or in bulk.',
        'data_flow' => 'Candidate rejected → Generate rejection email → Send to candidate email address → Update candidate status',
        'depends_on' => ['Candidate'],
        'business_domain' => 'HR & Recruitment',
    ],

    // ─── POLICIES ──────────────────────────────────────────────

    'ActivityLogPolicy' => [
        'business_summary' => 'Authorization policy for activity log access. Only admins can view the full audit trail. Project managers can see logs related to their projects. Engineers and vendors have no access.',
        'data_flow' => 'User tries to access activity logs → Policy checks role → Admin: full access → PM: project-scoped → Others: denied',
        'depends_on' => ['User', 'ActivityLog'],
        'business_domain' => 'Security',
    ],

    'ProjectPolicy' => [
        'business_summary' => 'Authorization policy for project operations. Admins can do everything. Project Managers can only view/edit their assigned projects. Engineers and Vendors can only view their assigned project data.',
        'data_flow' => 'User tries project action → Policy checks role + assignment → Admin: allow all → PM: check assignment → Engineer/Vendor: read-only if assigned',
        'depends_on' => ['User', 'Project'],
        'business_domain' => 'Security',
    ],

    'StorePolicy' => [
        'business_summary' => 'Authorization policy for store/warehouse operations. Controls who can create, view, and manage inventory stores. Admins and assigned PMs can manage stores; engineers can only view.',
        'data_flow' => 'User tries store action → Policy checks role + project assignment → Admin: allow all → PM: check project → Engineer: view only',
        'depends_on' => ['User', 'Stores'],
        'business_domain' => 'Security',
    ],

    'UserPolicy' => [
        'business_summary' => 'Authorization policy for user management. Admins can manage all users. Project Managers can manage engineers and vendors within their projects. Users can only edit their own profile.',
        'data_flow' => 'User tries to manage another user → Policy checks role hierarchy → Admin: all → PM: project staff → User: self only',
        'depends_on' => ['User'],
        'business_domain' => 'Security',
    ],

    // ─── SERVICE PROVIDERS (remaining) ──────────────────────────

    'AppServiceProvider' => [
        'business_summary' => 'Core application service provider. Registers global application services, view composers, and model observers. Sets up default Eloquent behaviors and pagination configuration.',
        'data_flow' => 'Application boots → AppServiceProvider registers services → Configures defaults → Services available throughout lifecycle',
        'depends_on' => [],
        'business_domain' => 'Architecture',
    ],

    'AuthServiceProvider' => [
        'business_summary' => 'Authentication and authorization service provider. Registers Gate definitions, Policy mappings (e.g., Project → ProjectPolicy), and custom auth guards used across the application.',
        'data_flow' => 'Application boots → Register policies → Map models to policies → Gate/Policy checks available in controllers/views',
        'depends_on' => ['ProjectPolicy', 'StorePolicy', 'UserPolicy', 'ActivityLogPolicy'],
        'business_domain' => 'Security',
    ],

    'BroadcastServiceProvider' => [
        'business_summary' => 'Real-time broadcasting configuration provider. Sets up channel authentication for WebSocket broadcasts (if used). Registers broadcast channel authorization callbacks.',
        'data_flow' => 'Application boots → Register broadcast channels → Authenticate channel access → Enable real-time notifications',
        'depends_on' => [],
        'business_domain' => 'Architecture',
    ],

    'EventServiceProvider' => [
        'business_summary' => 'Event-listener mapping provider. Registers all event-to-listener mappings, enabling the event-driven architecture. Maps events like InventoryDispatched to listeners like LogInventoryHistory.',
        'data_flow' => 'Application boots → Register event → listener mappings → Events dispatched → Matching listeners execute',
        'depends_on' => [],
        'business_domain' => 'Architecture',
    ],

    'RouteServiceProvider' => [
        'business_summary' => 'Route configuration provider. Loads route files (web.php, api.php), sets API rate limiting, configures route model binding, and defines the application\'s URL namespace.',
        'data_flow' => 'Application boots → Load web routes + API routes → Apply middleware groups → Rate limiting configured → Routes ready to serve',
        'depends_on' => [],
        'business_domain' => 'Architecture',
    ],

    // ─── CONSOLE COMMANDS ──────────────────────────────────────

    'BackupDatabase' => [
        'business_summary' => 'Artisan console command for automated database backup. Creates a compressed SQL dump of the entire database and stores it in the configured backup location. Can be scheduled via Laravel\'s task scheduler for daily/weekly backups.',
        'data_flow' => 'php artisan db:backup → Connect to MySQL → mysqldump → Compress → Store in backup directory → Log success/failure',
        'depends_on' => [],
        'business_domain' => 'System Administration',
    ],

    'ReadExcelFile' => [
        'business_summary' => 'Artisan console command for reading and debugging Excel files from the command line. Used by developers to inspect Excel file structure, validate formats, and troubleshoot import issues without going through the web UI.',
        'data_flow' => 'php artisan excel:read {file} → Parse Excel → Display headers + sample rows → Report format issues',
        'depends_on' => [],
        'business_domain' => 'System Administration',
    ],

    'Kernel' => [
        'business_summary' => 'Console kernel — defines the application\'s scheduled task list. Configures recurring jobs like database backups, cache clearing, and queue monitoring. Also registers custom Artisan commands.',
        'data_flow' => 'Cron triggers → Laravel scheduler checks schedule → Due tasks execute → Log output → Next check cycle',
        'depends_on' => ['BackupDatabase', 'ReadExcelFile'],
        'business_domain' => 'System Administration',
    ],

    // ─── EXCEPTIONS ──────────────────────────────────────────────

    'Handler' => [
        'business_summary' => 'Global exception handler for the application. Defines how errors are reported (logged to storage/logs) and rendered (JSON for API, HTML for web). Customizes error responses for 404, 403, 500, and validation errors.',
        'data_flow' => 'Exception thrown → Handler catches → Report: Log to file/external service → Render: JSON for API / Blade view for web → Return error response',
        'depends_on' => [],
        'business_domain' => 'System Administration',
    ],

    // ─── REPOSITORIES ──────────────────────────────────────────

    'BaseRepository' => [
        'business_summary' => 'Base repository implementing common CRUD operations. All repositories extend this class to inherit standard data access methods (all, find, create, update, delete) with consistent error handling.',
        'data_flow' => 'Service calls repository method → BaseRepository executes Eloquent query → Returns model or collection',
        'depends_on' => [],
        'business_domain' => 'Architecture',
    ],

    'MeetingRepository' => [
        'business_summary' => 'Data access layer for meetings. Provides optimized queries for fetching meetings with discussion points, filtering by date/project, and aggregating meeting statistics.',
        'data_flow' => 'MeetingService → MeetingRepository → Optimized Eloquent queries → Meet + DiscussionPoint + FollowUp data',
        'depends_on' => ['Meet', 'DiscussionPoint', 'FollowUp'],
        'business_domain' => 'Meetings & Collaboration',
    ],

    'ProjectRepository' => [
        'business_summary' => 'Data access layer for projects. Handles project queries with eager loading of staff, sites, and stores. Provides scoped queries based on user role and assignment.',
        'data_flow' => 'ProjectService → ProjectRepository → Role-scoped Eloquent queries → Project data with relationships',
        'depends_on' => ['Project', 'User'],
        'business_domain' => 'Project Management',
    ],

    'SiteRepository' => [
        'business_summary' => 'Data access layer for sites/panchayats. Provides queries for sites with pole aggregation, district filtering, and ward-level statistics.',
        'data_flow' => 'SiteService → SiteRepository → Eloquent queries with sub-queries for pole counts → Site data with statistics',
        'depends_on' => ['Site', 'Streetlight', 'Pole'],
        'business_domain' => 'Site Management',
    ],

    'TaskRepository' => [
        'business_summary' => 'Data access layer for tasks/targets. Handles complex task queries involving staff assignments, site relationships, status filtering, and completion statistics.',
        'data_flow' => 'TaskService → TaskRepository → Eloquent queries with multiple joins → Task data with relationships + statistics',
        'depends_on' => ['Task', 'StreetlightTask', 'User', 'Streetlight'],
        'business_domain' => 'Field Operations',
    ],

    'UserRepository' => [
        'business_summary' => 'Data access layer for users/staff. Provides role-filtered queries, project-scoped user lists, and performance data aggregation queries.',
        'data_flow' => 'UserService → UserRepository → Role-filtered Eloquent queries → User data with assignments',
        'depends_on' => ['User', 'Role'],
        'business_domain' => 'Staff & HR',
    ],
];
