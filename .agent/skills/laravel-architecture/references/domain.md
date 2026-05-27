# Domain Knowledge — Full Reference

## Table of Contents
1. [System Overview](#1-system-overview)
2. [User Roles & Permissions](#2-user-roles--permissions)
3. [Modules](#3-modules)
4. [Key Business Rules](#4-key-business-rules)
5. [Data Models & Relationships](#5-data-models--relationships)
6. [Import/Export System](#6-importexport-system)
7. [Integration Points](#7-integration-points)

---

## 1. System Overview

**SLLDM** (Solar/Streetlight Lifecycle & Delivery Management) is a CRM for managing solar installation projects across India's administrative hierarchy:

```
State → District → Block → Panchayat → Ward → Pole
```

It handles the full lifecycle: planning, resource allocation, inventory management, field operations tracking, billing/expenses, performance analytics, and compliance reporting.

---

## 2. User Roles & Permissions

| Role (Enum Value) | Primary Goal | Access Scope |
|--------------------|-------------|--------------|
| ADMIN (0) | System oversight | Full access to all projects, users, and settings |
| SITE_ENGINEER (1) | Field operations | Assigned tasks and sites only |
| PROJECT_MANAGER (2) | Project oversight | Assigned projects, direct reports |
| VENDOR (3) | Installation work | Assigned targets and dispatched inventory |
| STORE_INCHARGE (4) | Inventory management | Managed store inventory and dispatch |
| COORDINATOR (5) | Admin coordination | Assigned project data |
| HR_MANAGER (6) | Recruitment | Candidate pipeline |
| REPORTING_MANAGER (7) | Reports & analytics | Performance metrics access |
| VERTICAL_HEAD (8) | Department oversight | Department-wide data |
| CLIENT (10) | Progress monitoring | Read-only access to assigned projects |
| REVIEW_MEETING_ONLY (11) | Meeting participation | Review meetings module only |

### Access Control Hierarchy
- **Admins**: Unrestricted access
- **Project Managers**: Can manage assigned projects and `manager_id` direct reports
- **Field Roles** (Engineer, Vendor): Limited to assigned tasks/sites
- **Support Roles** (Store Incharge, Coordinator): Functional area access
- **External** (Client, Review Meeting Only): Read-only, minimal scope

---

## 3. Modules

### 3.1 Dashboard
Role-based analytics center. Admin sees all projects; PM sees assigned projects.
- Project performance analytics (district-wise)
- Meeting summary analytics
- TA/DA bills analytics
- Competitive leaderboards
- Excel export of filtered views

### 3.2 Projects
Master container for all project activities.
- Rooftop and Streetlight project types with specialized workflows
- Staff/vendor assignment with automatic target reassignment on removal
- Store management for streetlight projects
- Multi-tab view: Sites, Targets, Staff, Vendors, Inventory, Stores

### 3.3 Sites
Physical installation locations.
- Rooftop: individual building sites
- Streetlight: panchayats/wards with multiple poles
- Pole management: Surveyed vs Installed tabs
- Excel import/export for bulk operations
- Ward-level filtering

### 3.4 Inventory & Stores
Material lifecycle management.
- Procurement → Storage → Dispatch → Consumption → Return/Replace
- Streetlight items: SL01=Panel, SL02=Luminary, SL03=Battery, SL04=Structure
- Serial number and QR code tracking
- District-based inventory locking (streetlight items locked to dispatched district)
- Bulk dispatch from Excel

### 3.5 Tasks & Targets
Work assignment management.
- Rooftop: individual installation tasks
- Streetlight: targets (panchayat-level assignments) → individual pole installations
- State machine: Pending → In Progress → Completed/Blocked
- Bulk import, reassignment, and deletion via queue jobs

### 3.6 Meetings
Structured communication and collaboration.
- Review meetings with discussion points and follow-ups
- Discussion point lifecycle: Open → In Progress → Resolved
- Meeting notes with version history
- Whiteboard for collaborative notes
- PDF/Excel export

### 3.7 Performance
Analytics and performance tracking.
- Personal, subordinates, leaderboard, and trend views
- Role-based filtering
- Configurable time periods
- Export for executive reporting

### 3.8 Billing (TA/DA & Conveyance)
Expense management.
- Travel allowance and daily allowance claims
- Conveyance claims with vehicle tracking
- Approval workflows: Pending → Accepted/Rejected
- Bulk processing and status updates
- Supporting document uploads

### 3.9 Staff & Vendor Management
Team composition and profile management.
- Staff creation, import, profile management
- Project assignment with `manager_id` hierarchical restrictions
- Automatic target reassignment when staff/vendor removed
- Profile pictures via S3 upload

### 3.10 HRM/Candidates
Recruitment lifecycle.
- Candidate import from Excel
- Recruitment email with application link
- Public application form at `/apply-now/{id}`
- Document management
- Status tracking: Pending → In Review → Accepted/Rejected

### 3.11 JICR (Job Inspection Completion Report)
Official inspection reports for streetlight projects.
- Geographic area selection via cascading AJAX dropdowns
- Date range filtering
- PDF generation via DomPDF

### 3.12 Backup & Export
Data archival.
- Project-specific multi-sheet Excel exports
- Data transformation: booleans → Yes/No, enums → labels, IDs → names
- Rooftop sheets: Project Details, Sites, Staff, Inventory, Tasks, Sites Done
- Streetlight sheets: Project Details, Sites, Store Inventory, Staff, Vendors, Targets, Poles

---

## 4. Key Business Rules

### Target Reassignment Logic
When a staff member or vendor is removed from a project:
- **Admin removing**: Targets reassigned to Project Manager (or Admin if no PM exists)
- **PM removing**: Targets reassigned to self (the Project Manager)
- Prevents orphaned work assignments

### District-Based Inventory Locking
- Streetlight inventory items are locked to the district they were dispatched to
- Cross-district consumption is blocked with an error message
- Ensures accurate per-district tracking

### Role-Based Assignment Restrictions
- **Admins**: Can assign any staff/vendor to any project
- **Project Managers**: Only assign staff/vendors where `manager_id` matches
- Maintains organizational hierarchy

### Task State Machine
```
Pending ─────→ In Progress ─────→ Completed
                    │
                    └──→ Blocked ──→ In Progress (re-entry)
```
Transitions are validated via `TaskStatus::canTransitionTo()`. Completed is terminal.

### Pole Installation Lifecycle
```
Survey (GPS capture) → Installation (equipment mounting) → Commissioning (power-on + RMS)
```
Each phase updates different fields on the `Pole` record.

---

## 5. Data Models & Relationships

### Core Models (36 total)

| Model | Table | Key Relationships |
|-------|-------|-------------------|
| `User` | `users` | belongsToMany Projects, hasMany Tasks, Poles, Meets |
| `Project` | `projects` | hasMany Sites/Streetlights, Tasks, Stores; belongsToMany Users |
| `Site` | `sites` | belongsTo Project; hasMany Tasks |
| `Streetlight` | `streetlights` | belongsTo Project; hasMany StreetlightTasks, Poles |
| `Task` | `tasks` | belongsTo Project, Site, User (assignee) |
| `StreetlightTask` | `streetlight_tasks` | belongsTo Project, Streetlight; assigned to engineer + vendor |
| `Pole` | `poles` | belongsTo StreetlightTask, Streetlight; links to Inventory |
| `Inventory` | `inventories` | Rooftop project items |
| `InventroyStreetLightModel` | `streetlight_inventories` | Streetlight items with QR/serial |
| `InventoryDispatch` | `inventory_dispatches` | Dispatch records linking inventory to vendors |
| `InventoryHistory` | `inventory_histories` | Tracking inventory lifecycle events |
| `Stores` | `stores` | belongsTo Project; hasMany Inventory items |
| `Meet` | `meets` | hasMany DiscussionPoints, FollowUps; belongsToMany Users |
| `DiscussionPoint` | `discussion_points` | belongsTo Meet; hasMany Updates |
| `DiscussionPointUpdates` | `discussion_point_updates` | Progress updates on discussion points |
| `FollowUp` | `follow_ups` | belongsTo Meet |
| `Whiteboard` | `whiteboards` | belongsTo Meet |
| `MeetingNoteHistory` | `meeting_note_histories` | Version history for meeting notes |
| `Tada` | `tadas` | TA/DA expense claims |
| `Conveyance` | `conveyances` | Conveyance expense claims |
| `Journey` | `journeys` | Journey details within TA/DA claims |
| `HotelExpense` | `hotel_expenses` | Hotel expenses within TA/DA |
| `dailyfare` | `dailyfares` | Daily allowance rates |
| `travelfare` | `travelfares` | Travel fare rates |
| `Vehicle` | `vehicles` | Vehicle types for conveyance |
| `Candidate` | `candidates` | HR recruitment candidates |
| `State` | `states` | Indian states |
| `City` | `cities` | Cities (for HR/billing) |
| `DistrictCode` | `district_codes` | District administrative codes |
| `Role` | `roles` | Spatie permission roles |
| `Permission` | `permissions` | Spatie permissions |
| `UserCategory` | `user_categories` | User expense categories |
| `ActivityLog` | `activity_logs` | Spatie activity log records |
| `PoleImportJob` | `pole_import_jobs` | Tracking bulk pole import status |
| `TargetDeletionJob` | `target_deletion_jobs` | Tracking bulk target deletion status |
| `RmsPushLog` | `rms_push_logs` | RMS sync attempt logs |

---

## 6. Import/Export System

### Import Classes (`app/Imports/`)
| Class | Purpose |
|-------|---------|
| `StaffImport` | Bulk staff creation from Excel |
| `VendorImport` | Bulk vendor creation |
| `SiteImport` | Site creation from Excel |
| `StreetlightImport` | Streetlight site import |
| `TargetImport` | Bulk streetlight target creation |
| `SitePoleImport` | Pole creation within a site |
| `StreetlightPoleImport` | Streetlight pole import |
| `InventoryImport` | Rooftop inventory import |
| `InventroyStreetLight` | Streetlight inventory import |
| `InventoryDispatchImport` | Bulk dispatch from Excel |
| `CandidatesImport` | HR candidate import |

### Export Classes (`app/Exports/`)
| Class | Purpose |
|-------|---------|
| `TasksExport` | Task/target data export |
| `InventoryExport` | Inventory stock export |
| `InventoryImportFormatExport` | Download blank import template |
| `StreetlightPoleImportFormatExport` | Download pole import template |

### Import Best Practices
- Validate data before inserting rows
- Use chunked reading for large files (>10K rows)
- Queue large imports via `ProcessPoleImportChunk` job
- Return meaningful error messages for validation failures
- Never skip validation in production (it was temporarily disabled for a large demo import — see conversation `904f77ee`)

---

## 7. Integration Points

### AWS S3
- **Purpose**: File storage for avatars, documents, application materials
- **Config**: `AWS_BUCKET=sugslloyd`, `AWS_DEFAULT_REGION=ap-south-1`
- **Usage**: `Storage::disk('s3')->put(...)`, `Storage::disk('s3')->url(...)`

### WhatsApp API (AiSensy)
- **Purpose**: Push notifications and messaging
- **Config**: `WHATSAPP_API_URL`, `WHATSAPP_API_KEY`
- **Endpoint**: `https://backend.api-wa.co/campaign/digintra/api/v2`

### RMS (Remote Monitoring System)
- **Purpose**: Push pole installation data for remote monitoring
- **Components**: `RMSController`, `SyncPolesToRmsJob`
- **Flow**: Manual push from UI or bulk push via `bulkPushRms`
- **NOT automatic**: No observer or event listener. Always user-initiated.

### Email (SMTP)
- **Purpose**: Recruitment emails, system notifications
- **Provider**: Hostinger SMTP (`smtp.hostinger.com:465`)
- **Config**: `MAIL_*` env variables
