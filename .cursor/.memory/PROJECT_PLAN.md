# Laravel CRM - Comprehensive Project Plan

**Last Updated**: 2025-12-16  
**Project Type**: Solar Project Management CRM System  
**Framework**: Laravel 10  
**Database**: MySQL

---

## Executive Summary

This is a comprehensive **Customer Relationship Management (CRM) system** specifically designed for managing **solar installation projects** in India. The system supports two primary project types:

1. **Rooftop Solar Projects** - Solar panel installations on building rooftops
2. **Streetlight Projects** - Solar streetlight installations in rural/urban areas (panchayats, wards, districts)

The system manages the entire project lifecycle from planning to completion, including staff/vendor management, inventory tracking, task assignment, billing, meetings, performance monitoring, and reporting.

---

## 1. Business Domain Overview

### 1.1 Core Business Model

The system manages solar installation projects with the following key entities:

- **Projects**: Master container for all work (Rooftop or Streetlight type)
- **Sites**: Physical locations where installations occur
  - For Rooftop: Individual building sites
  - For Streetlight: Poles in panchayats/wards
- **Tasks**: Work assignments assigned to engineers/vendors
- **Inventory**: Material tracking (panels, batteries, structures, luminaries)
- **Stores**: Physical warehouses managing inventory
- **Users**: Staff, vendors, managers with role-based access

### 1.2 Geographic Hierarchy (Streetlight Projects)

The system handles Indian administrative hierarchy:
- **State** → **District** → **Block** → **Panchayat** → **Ward** → **Pole**

Each streetlight pole is located within this hierarchy and can be tracked individually.

### 1.3 Project Workflow

```
Project Creation → Staff/Vendor Assignment → Site Creation → Task Assignment 
→ Inventory Dispatch → Installation → Quality Check → Billing → Completion
```

---

## 2. User Roles & Permissions

### 2.1 Role Hierarchy

The system uses `App\Enums\UserRole` enum (never hardcoded integers):

| Role | Value | Description |
|------|-------|-------------|
| **ADMIN** | 0 | Full system access, can manage all projects |
| **SITE_ENGINEER** | 1 | Field engineers managing on-ground operations |
| **PROJECT_MANAGER** | 2 | Oversees projects, manages site engineers and vendors |
| **VENDOR** | 3 | External contractors performing installation work |
| **STORE_INCHARGE** | 4 | Manages inventory and material dispatch |
| **COORDINATOR** | 5 | Administrative coordination role |
| **HR_MANAGER** | 6 | Handles candidate recruitment |
| **REPORTING_MANAGER** | 7 | Reporting and analytics |
| **VERTICAL_HEAD** | 8 | Vertical/department head |
| **CLIENT** | 10 | External stakeholders with limited view access |
| **REVIEW_MEETING_ONLY** | 11 | Can only access review meetings module |

### 2.2 Key Permission Rules

1. **Project Assignment**:
   - Admin: Can assign any staff/vendor to any project
   - Project Manager: Can only assign staff/vendors where `manager_id = current_user->id` (their direct reports)

2. **Store Management**:
   - Only Admin can create stores
   - Store Incharge manages inventory within assigned stores

3. **Task Assignment**:
   - Project Manager assigns tasks to Site Engineers and Vendors
   - Tasks can be reassigned when staff/vendors are removed from projects

---

## 3. Core Modules

### 3.1 Projects Module

**Purpose**: Master container for all project-related data

**Key Features**:
- Create/edit rooftop or streetlight projects
- Project details: name, capacity, work order number, agreement details, dates
- Staff and vendor assignment/removal
- Project-specific inventory tracking
- Store management per project
- Multi-project support

**Models**:
- `Project` (project_type: 0=Rooftop, 1=Streetlight)
- `project_user` (pivot table with role)

**Key Functionality**:
- **Target Reassignment**: When staff/vendor removed, their tasks automatically reassigned to Project Manager (or Admin if no PM)
- **Project Types**: Different models for sites/tasks/inventory based on project_type

**Controllers**: `ProjectsController`

---

### 3.2 Sites Module

**Purpose**: Physical locations for installations

**Features**:
- Site creation for rooftop projects (building locations)
- Streetlight site creation (panchayat/ward-based)
- Site search and filtering
- Site import from Excel
- GPS coordinates tracking (survey and actual locations)
- Site status tracking (survey, installation, commissioning)

**Models**:
- `Site` (for rooftop projects)
- `Streetlight` (for streetlight projects)

**Controllers**: `SiteController`

---

### 3.3 Tasks Module

**Purpose**: Work assignment and tracking

**Features**:
- Task creation for rooftop installations
- Target assignment for streetlight poles
- Status tracking (Pending, In Progress, Completed, Blocked)
- Bulk operations (reassign, delete, import)
- Task export to Excel
- Engineer and vendor assignment

**Models**:
- `Task` (for rooftop projects)
- `StreetlightTask` (for streetlight projects/targets)

**Status Flow**:
```
Pending → In Progress → Completed
              ↓
          Blocked → In Progress
```

**Controllers**: `TasksController`, `API/TaskController`

---

### 3.4 Inventory Module

**Purpose**: Material and equipment tracking

**Features**:
- Inventory management per store
- Support for both project types:
  - **Rooftop**: Generic items (panels, inverters, cables, etc.)
  - **Streetlight**: Specific items (SL01=Panel, SL02=Luminary, SL03=Battery, SL04=Structure)
- QR code tracking
- Dispatch/return/replace operations
- Bulk dispatch from Excel
- Inventory history tracking
- District-based inventory locking (streetlight projects)
- SIM number tracking for luminaries (SL02 items)

**Key Inventory Items (Streetlight)**:
- SL01: Solar Panel
- SL02: Luminary (with SIM number)
- SL03: Battery
- SL04: Structure

**Models**:
- `Inventory` (rooftop)
- `InventroyStreetLightModel` (streetlight)
- `InventoryDispatch` (dispatch tracking)
- `InventoryHistory` (audit trail)

**Controllers**: `InventoryController`, `StoreController`

**Inventory Flow**:
```
Stock → Dispatch → Consumed → (Return/Replace) → Stock
```

---

### 3.5 Staff & Vendor Management

**Purpose**: User management and project assignment

**Features**:
- Staff listing with role-based filtering
- Vendor management (separate from staff)
- Profile management (avatar upload, contact details, bank details)
- Project assignment/removal with role-based permissions
- Bulk operations
- Staff import from Excel
- Target reassignment on removal

**Controllers**: `StaffController`, `VendorController`

**UI Features**:
- Modern card-based UI for staff/vendor assignment
- Two-column layout (Assigned/Available)
- Bulk selection and operations
- Search functionality
- Role-based grouping

---

### 3.6 Meetings Module

**Purpose**: Review meetings, discussions, and follow-ups

**Features**:
- Meeting creation and scheduling
- Participant management
- Meeting notes with history tracking
- Discussion points and updates
- Follow-up scheduling
- Whiteboard functionality
- PDF/Excel export
- Meeting types (status meetings, review meetings, etc.)

**Models**:
- `Meet`
- `DiscussionPoint`
- `DiscussionPointUpdates`
- `FollowUp`
- `Whiteboard`
- `MeetingNoteHistory`

**Controllers**: `MeetController`, `WhiteboardController`

---

### 3.7 Performance Module

**Purpose**: Track user performance and metrics

**Features**:
- Individual user performance tracking
- Subordinates performance (hierarchical view)
- Leaderboards by role
- Performance trends over time
- Export capabilities
- Role-based visibility

**Controllers**: `PerformanceController`

---

### 3.8 Billing Module (TA/DA & Conveyance)

**Purpose**: Travel allowance, daily allowance, and conveyance expense management

#### 3.8.1 TA/DA (Travel Allowance & Daily Allowance)

**Features**:
- Travel expense claims (journey tickets, PNR tracking)
- Hotel expense tracking (check-in, check-out, bills)
- Daily allowance calculation
- Miscellaneous expenses
- Status tracking (Pending, Accepted, Rejected)
- Bulk status updates
- Detailed expense reports

**Models**: `Tada`, `Journey`, `HotelExpense`

#### 3.8.2 Conveyance

**Features**:
- Vehicle-based conveyance claims
- Accept/reject workflow
- Bulk actions
- Expense tracking and reporting

**Models**: `Conveyance`, `Vehicle`

#### 3.8.3 Billing Settings

**Features**:
- Vehicle management
- Category management (user categories, expense categories)
- City-based category settings
- Allowed expense configuration
- User-specific settings

**Models**: `UserCategory`, `Vehicle`, `City`

**Controllers**: `ConvenienceController`

---

### 3.9 HRM/Candidates Module

**Purpose**: Recruitment and candidate management

**Features**:
- Candidate listing and import
- Email sending to candidates
- Document upload management
- Application form (public route)
- Application preview and submission
- Bulk operations
- Status tracking

**Models**: `Candidate`

**Public Routes**:
- `/apply-now/{id}` - Application form
- `/apply/preview` - Preview before submission
- `/apply/success` - Success page
- `/privacy-policy` - Privacy policy
- `/terms-and-conditions` - Terms and conditions

**Controllers**: `CandidateController`, `API/PreviewController`

---

### 3.10 JICR Module

**Purpose**: Generate Job Inspection Completion Reports (JICR) for streetlight projects

**Features**:
- District/Block/Panchayat/Ward selection via AJAX dropdowns
- Date range filtering
- PDF generation with pole details
- Project and assignment information
- Installation status reporting

**Controllers**: `JICRController`

**Use Case**: Generate official inspection reports for completed streetlight installations in specific geographic areas.

---

### 3.11 Backup & Export Module

**Purpose**: Data backup and project-specific exports

**Features**:
- Project-specific multi-sheet Excel exports
- Human-readable data transformations (enum values, boolean fields)
- Separate export structure for rooftop vs streetlight projects
- Backup file management (create, download, delete)

**Controllers**: `BackupController`

**Export Structure**:
- **Rooftop Projects**: Project Details, Sites, Staff, Inventory Used/Stock, Tasks, Sites Done
- **Streetlight Projects**: Project Details, Streetlight Sites, Store Inventory, Staff, Vendors, Targets, Installations (Poles)

---

### 3.12 Device Import Module

**Purpose**: Import device information from Excel

**Features**:
- Excel-based device import
- Validation and error reporting
- UI feedback for import results

**Controllers**: `DeviceController`

---

### 3.13 RMS Export Module

**Purpose**: Push panchayat data to RMS (Remote Management System)

**Features**:
- District/Block/Panchayat selection
- AJAX dropdowns for hierarchical selection
- Data export/push to RMS

**Controllers**: `RMSController`

---

## 4. Technical Architecture

### 4.1 Technology Stack

- **Framework**: Laravel 10
- **PHP**: 8.1+
- **Database**: MySQL
- **Frontend**: Blade templates, Bootstrap 5, jQuery, DataTables
- **PDF**: DomPDF (Barryvdh)
- **Excel**: Maatwebsite Excel
- **Storage**: AWS S3 (for avatars, documents)
- **Icons**: Material Design Icons (MDI)

### 4.2 Architecture Patterns

1. **Repository Pattern**: Interfaces and implementations for data access
2. **Service Layer**: Business logic separated from controllers
3. **Strategy Pattern**: Different strategies for rooftop vs streetlight inventory
4. **Policy-Based Authorization**: Laravel Policies for access control
5. **Enum Usage**: Type-safe enums for roles, statuses, project types

### 4.3 Key Services

- `DashboardService` - Dashboard analytics
- `InventoryService` - Inventory operations
- `TaskService` - Task management
- `MeetingService` - Meeting operations
- `PerformanceService` - Performance calculations
- `AnalyticsService` - Data analytics

### 4.4 Database Design Principles

- **Pivot Tables**: Many-to-many relationships (project_user, meet_user)
- **Soft Deletes**: Preserve data integrity
- **Foreign Keys**: Cascade deletes for related data
- **JSON Fields**: For flexible data (materials_consumed, miscellaneous)
- **Timestamps**: created_at, updated_at on all tables
- **Status Enums**: String enums for readable status values

---

## 5. Data Flow & Business Logic

### 5.1 Project Creation Flow

```
1. Create Project (Rooftop/Streetlight)
2. Assign Staff (Project Manager, Site Engineers, Store Incharge)
3. Assign Vendors
4. Create Stores (if streetlight project)
5. Create Sites
6. Assign Tasks/Targets
7. Dispatch Inventory
8. Track Installation
9. Generate Reports
```

### 5.2 Inventory Dispatch Flow (Streetlight)

```
1. Inventory added to Store (via import/manual entry)
2. Engineer/Vendor requests dispatch
3. Store Incharge dispatches items
4. Items tracked via QR codes
5. Items consumed when pole installed
6. District validation prevents cross-district consumption
7. Items can be returned or replaced
```

### 5.3 Target Reassignment Logic

**When staff/vendor removed from project**:

1. Find all tasks where removed user is assigned:
   - `StreetlightTask` where `engineer_id = removed_user_id`
   - `StreetlightTask` where `vendor_id = removed_user_id`
   - Filter by current project

2. Determine reassignment target:
   - **Admin removing**: Find Project Manager → If found, reassign to PM → Else reassign to Admin
   - **PM removing**: Always reassign to PM (themselves)

3. Update tasks:
   - Update `engineer_id` or `vendor_id` to reassigned user
   - Log reassignments
   - Preserve historical data (tasks not deleted)

4. Detach user from project:
   - Remove from `project_user` pivot table

---

## 6. UI/UX Patterns

### 6.1 Design System

- **Minimal, Professional Design**: Clean layouts, no excessive gradients
- **Consistent Typography**: Standardized font sizes and weights
- **Color Scheme**: Bootstrap 5 default with custom accents
- **Buttons**: `btn-outline-primary` for primary actions, consistent icon usage

### 6.2 Component Standards

- **DataTables**: Standardized datatable component with consistent column widths
- **Forms**: Collapsible inline forms, validation feedback
- **Cards**: Bordered cards for grouped content
- **Modals**: Bootstrap modals for confirmations and forms
- **Toasts**: SweetAlert2 for notifications

### 6.3 Responsive Design

- Mobile-friendly layouts
- Sidebar collapse on small screens
- Responsive tables and forms
- Touch-friendly buttons on mobile

---

## 7. Import/Export Capabilities

### 7.1 Import Features

- **Projects**: Excel import with validation
- **Sites**: Excel import (rooftop and streetlight)
- **Inventory**: Excel import with format templates
- **Staff**: Excel import
- **Vendors**: Excel import
- **Candidates**: Excel import
- **Tasks/Targets**: Excel import
- **Bulk Dispatch**: Excel-based dispatch

### 7.2 Export Features

- **Projects**: Multi-sheet Excel export
- **Tasks**: Excel export
- **Inventory**: Format template download
- **Meetings**: PDF and Excel export
- **Performance**: Performance reports
- **Poles**: Surveyed/installed poles export
- **Backup**: Complete project backups

---

## 8. Integration Points

### 8.1 External Systems

- **AWS S3**: File storage (avatars, documents)
- **RMS**: Remote Management System (data push)
- **WhatsApp**: Messaging integration (via `WhatsappHelper`)

### 8.2 APIs

- AJAX endpoints for dropdown population
- API controllers for mobile app support (if needed)
- QR code scanning integration

---

## 9. Security & Authorization

### 9.1 Authentication

- Laravel authentication system
- Password reset functionality
- Login restrictions (disableLogin flag)

### 9.2 Authorization

- Role-based access control (RBAC)
- Policy-based authorization (Laravel Policies)
- Middleware for route protection
- Hierarchical permissions (PM can only manage their team)

### 9.3 Data Protection

- CSRF protection on all forms
- Input validation and sanitization
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade templating)

---

## 10. Testing & Quality Assurance

### 10.1 Testing Requirements

Per `.cursorrules` and `TESTING_RULES.md`:

1. **No Assumptions**: Verify all code against actual database schema
2. **Browser Testing**: Test all user-facing features in browser
3. **Binary Status**: Use only DONE or NOT DONE
4. **Concrete Evidence**: Report exact behavior, not assumptions

### 10.2 Testing Checklist

- ✅ Staff/Vendor assignment and removal
- ✅ Target reassignment logic
- ✅ Inventory dispatch/return/replace
- ✅ Task creation and status updates
- ✅ Meeting creation and notes
- ✅ Billing workflows
- ✅ Import/export functionality
- ✅ Authorization and permissions

---

## 11. Current Status & Pending Work

### 11.1 Completed Modules

✅ **Global Foundations**:
- Project staff/vendor management UI
- Datatable fixes and standardization
- Public pages verification

✅ **Module 1 - Vendors**:
- Vendor CRUD flows
- Vendor show page improvements
- Avatar upload functionality

✅ **Module 13 - Public Pages**:
- Application forms
- Privacy policy and terms

### 11.2 Pending Modules

🔄 **Module 2 - Tasks & Poles**:
- Tasks CRUD completion
- Status updates
- Poles-related views

🔄 **Module 3 - Sites**:
- Sites CRUD completion
- Import functionality
- Ward poles behavior

🔄 **Module 4 - Inventory**:
- Inventory CRUD finalization
- Dispatch/return/replace flows
- Pagination

🔄 **Module 5 - Meetings**:
- Meeting flows verification
- Whiteboard functionality
- Export testing

🔄 **Module 6 - Performance**:
- Detailed views implementation
- Leaderboard and trends
- Export functionality

🔄 **Module 7 - Billing**:
- Route fixes
- TA/DA flows
- Conveyance flows
- Settings flows

🔄 **Module 8 - JICR**:
- Route fixes
- AJAX dropdowns
- PDF generation

🔄 **Module 9 - Backup**:
- Data transformation service
- Multi-sheet exports
- Project-specific exports

🔄 **Module 10 - HRM**:
- Route fixes
- Candidate flows
- Email sending

🔄 **Module 11 - Device Import**:
- Route fixes
- Import testing

🔄 **Module 12 - RMS Export**:
- Flow completion
- Integration testing

---

## 12. Development Guidelines

### 12.1 Code Standards

Per `.cursorrules`:

1. **Read First**: Always read Model and Migration files before writing queries
2. **No Magic Numbers**: Use enums (`UserRole`, `TaskStatus`, `ProjectType`) instead of integers
3. **Validation**: Strict validation in controllers matching DB constraints
4. **Logging**: Use `Log::info()` with structured arrays
5. **Routes**: Prefer controllers over closures

### 12.2 Database Standards

1. **Always Check Schema**: Read migration files before assuming column types
2. **Pivot Tables**: Include pivot data when using `syncWithoutDetaching()` or `attach()`
3. **Foreign Keys**: Use qualified column names in joins to avoid ambiguity
4. **Transactions**: Wrap critical operations in database transactions

### 12.3 Testing Standards

1. **Browser Testing**: Test all UI changes in browser before marking complete
2. **No Guess Words**: Report exact observed behavior
3. **Binary Status**: Use DONE or NOT DONE only
4. **Evidence**: Provide concrete test results

---

## 13. Key Files & Directories

### 13.1 Core Directories

```
app/
  ├── Enums/           # UserRole, TaskStatus, ProjectType, InstallationPhase
  ├── Models/          # Eloquent models
  ├── Http/
  │   ├── Controllers/ # Main controllers
  │   │   └── API/     # API controllers
  │   ├── Middleware/  # Custom middleware
  │   └── Requests/    # Form request validation
  ├── Services/        # Business logic services
  ├── Repositories/    # Data access layer
  ├── Policies/        # Authorization policies
  ├── Imports/         # Excel import classes
  └── Exports/         # Excel export classes

resources/views/       # Blade templates
routes/
  ├── web.php         # Web routes
  └── api.php         # API routes

database/
  ├── migrations/     # Database migrations
  └── schema/         # Database schema SQL
```

### 13.2 Key Configuration Files

- `current_task.md` - Task tracking
- `TESTING_RULES.md` - Testing guidelines
- `.cursorrules` - Development rules
- `composer.json` - PHP dependencies

---

## 14. Business Rules Summary

### 14.1 Project Rules

1. Projects have a type (Rooftop=0, Streetlight=1)
2. Different models used based on project type (Site vs Streetlight, Task vs StreetlightTask)
3. Stores only created for streetlight projects (typically)
4. Streetlight projects use geographic hierarchy (State→District→Block→Panchayat→Ward→Pole)

### 14.2 Inventory Rules

1. Streetlight items are restricted (SL01-SL04 only)
2. SIM numbers required and unique for luminaries (SL02)
3. District-based locking prevents cross-district consumption
4. Inventory can be dispatched, consumed, returned, or replaced
5. Inventory history tracked for audit purposes

### 14.3 Assignment Rules

1. Admin can assign anyone to any project
2. Project Manager can only assign their direct reports (manager_id check)
3. When removed, tasks automatically reassigned to PM (or Admin)
4. Pivot role stored in project_user.role

### 14.4 Billing Rules

1. TA/DA claims include journey, hotel, and miscellaneous expenses
2. Conveyance claims are vehicle-based
3. Status workflow: Pending → Accepted/Rejected
4. Bulk operations supported for status updates

---

## 15. Future Enhancements

### 15.1 Planned Features

- Notification system for target reassignments
- Enhanced audit trail for inventory operations
- Bulk target reassignment before staff removal
- Historical tracking for reassigned tasks
- Mobile app support (API endpoints ready)
- Advanced analytics and reporting
- Real-time updates via WebSockets
- Multi-language support

### 15.2 Technical Improvements

- API versioning
- Caching strategy for frequently accessed data
- Queue jobs for heavy operations (exports, imports)
- Event-driven architecture for notifications
- Comprehensive unit and feature tests
- API documentation (Swagger/OpenAPI)

---

## 16. Deployment & Infrastructure

### 16.1 Environment Requirements

- PHP 8.1+
- MySQL 5.7+
- Composer
- Node.js (for frontend assets)
- AWS S3 bucket (for file storage)

### 16.2 Configuration

- `.env` file for environment variables
- Database connection settings
- AWS S3 credentials
- Mail configuration
- Queue configuration

---

## 17. Documentation References

- `current_task.md` - Current task tracking and status
- `TESTING_RULES.md` - Testing guidelines and requirements
- `STAFF_VENDOR_MANAGEMENT_SUMMARY.md` - Staff/vendor management implementation details
- `VENDOR_SHOW_PAGE_IMPROVEMENTS.md` - Vendor page improvements
- `test_report.md` - Test reports and results
- `UI_LAYOUT_TODOS.md` - UI consistency tasks

---

## Conclusion

This Laravel CRM system is a comprehensive solution for managing solar installation projects from inception to completion. It handles complex workflows involving multiple user roles, geographic hierarchies, inventory management, billing, and reporting. The system is built with modern Laravel practices, emphasizing code quality, security, and maintainability.

**Key Strengths**:
- ✅ Role-based access control
- ✅ Support for multiple project types
- ✅ Comprehensive inventory tracking
- ✅ Geographic hierarchy support
- ✅ Audit trails and history tracking
- ✅ Flexible import/export capabilities
- ✅ Modern UI with responsive design

**Areas for Improvement**:
- 🔄 Complete remaining module implementations
- 🔄 Comprehensive testing coverage
- 🔄 API documentation
- 🔄 Performance optimization
- 🔄 Enhanced error handling and logging

---

**Document Version**: 1.0  
**Last Updated**: 2025-12-16  
**Maintained By**: Development Team

