# Graph Report - laravel_crm  (2026-06-01)

## Corpus Check
- 466 files · ~292,403 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2262 nodes · 4263 edges · 85 communities detected
- Extraction: 51% EXTRACTED · 49% INFERRED · 0% AMBIGUOUS · INFERRED: 2092 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- [[_COMMUNITY_Community 0|Community 0]]
- [[_COMMUNITY_Community 1|Community 1]]
- [[_COMMUNITY_Community 2|Community 2]]
- [[_COMMUNITY_Community 3|Community 3]]
- [[_COMMUNITY_Community 4|Community 4]]
- [[_COMMUNITY_Community 5|Community 5]]
- [[_COMMUNITY_Community 6|Community 6]]
- [[_COMMUNITY_Community 7|Community 7]]
- [[_COMMUNITY_Community 8|Community 8]]
- [[_COMMUNITY_Community 9|Community 9]]
- [[_COMMUNITY_Community 10|Community 10]]
- [[_COMMUNITY_Community 11|Community 11]]
- [[_COMMUNITY_Community 12|Community 12]]
- [[_COMMUNITY_Community 13|Community 13]]
- [[_COMMUNITY_Community 14|Community 14]]
- [[_COMMUNITY_Community 15|Community 15]]
- [[_COMMUNITY_Community 16|Community 16]]
- [[_COMMUNITY_Community 17|Community 17]]
- [[_COMMUNITY_Community 18|Community 18]]
- [[_COMMUNITY_Community 19|Community 19]]
- [[_COMMUNITY_Community 21|Community 21]]
- [[_COMMUNITY_Community 22|Community 22]]
- [[_COMMUNITY_Community 24|Community 24]]
- [[_COMMUNITY_Community 25|Community 25]]
- [[_COMMUNITY_Community 27|Community 27]]
- [[_COMMUNITY_Community 31|Community 31]]
- [[_COMMUNITY_Community 35|Community 35]]
- [[_COMMUNITY_Community 38|Community 38]]
- [[_COMMUNITY_Community 39|Community 39]]
- [[_COMMUNITY_Community 40|Community 40]]
- [[_COMMUNITY_Community 41|Community 41]]
- [[_COMMUNITY_Community 49|Community 49]]
- [[_COMMUNITY_Community 50|Community 50]]
- [[_COMMUNITY_Community 51|Community 51]]
- [[_COMMUNITY_Community 52|Community 52]]
- [[_COMMUNITY_Community 53|Community 53]]
- [[_COMMUNITY_Community 54|Community 54]]
- [[_COMMUNITY_Community 55|Community 55]]
- [[_COMMUNITY_Community 56|Community 56]]
- [[_COMMUNITY_Community 57|Community 57]]
- [[_COMMUNITY_Community 58|Community 58]]
- [[_COMMUNITY_Community 59|Community 59]]
- [[_COMMUNITY_Community 60|Community 60]]
- [[_COMMUNITY_Community 61|Community 61]]
- [[_COMMUNITY_Community 62|Community 62]]
- [[_COMMUNITY_Community 63|Community 63]]
- [[_COMMUNITY_Community 64|Community 64]]
- [[_COMMUNITY_Community 65|Community 65]]
- [[_COMMUNITY_Community 66|Community 66]]
- [[_COMMUNITY_Community 67|Community 67]]
- [[_COMMUNITY_Community 68|Community 68]]
- [[_COMMUNITY_Community 69|Community 69]]
- [[_COMMUNITY_Community 70|Community 70]]
- [[_COMMUNITY_Community 71|Community 71]]
- [[_COMMUNITY_Community 72|Community 72]]
- [[_COMMUNITY_Community 98|Community 98]]
- [[_COMMUNITY_Community 99|Community 99]]
- [[_COMMUNITY_Community 100|Community 100]]
- [[_COMMUNITY_Community 101|Community 101]]
- [[_COMMUNITY_Community 102|Community 102]]
- [[_COMMUNITY_Community 103|Community 103]]
- [[_COMMUNITY_Community 104|Community 104]]
- [[_COMMUNITY_Community 105|Community 105]]
- [[_COMMUNITY_Community 106|Community 106]]
- [[_COMMUNITY_Community 107|Community 107]]
- [[_COMMUNITY_Community 108|Community 108]]
- [[_COMMUNITY_Community 109|Community 109]]
- [[_COMMUNITY_Community 110|Community 110]]
- [[_COMMUNITY_Community 111|Community 111]]
- [[_COMMUNITY_Community 112|Community 112]]
- [[_COMMUNITY_Community 113|Community 113]]
- [[_COMMUNITY_Community 114|Community 114]]
- [[_COMMUNITY_Community 115|Community 115]]
- [[_COMMUNITY_Community 116|Community 116]]
- [[_COMMUNITY_Community 117|Community 117]]
- [[_COMMUNITY_Community 118|Community 118]]
- [[_COMMUNITY_Community 119|Community 119]]
- [[_COMMUNITY_Community 120|Community 120]]
- [[_COMMUNITY_Community 121|Community 121]]
- [[_COMMUNITY_Community 122|Community 122]]
- [[_COMMUNITY_Community 123|Community 123]]
- [[_COMMUNITY_Community 124|Community 124]]
- [[_COMMUNITY_Community 125|Community 125]]
- [[_COMMUNITY_Community 126|Community 126]]
- [[_COMMUNITY_Community 128|Community 128]]

## God Nodes (most connected - your core abstractions)
1. `StreetlightTask` - 66 edges
2. `InventoryDispatch` - 44 edges
3. `values()` - 41 edges
4. `Site` - 39 edges
5. `Task` - 33 edges
6. `ConvenienceController` - 33 edges
7. `InventroyStreetLightModel` - 32 edges
8. `TaskController` - 31 edges
9. `SettingsController` - 30 edges
10. `StaffController` - 26 edges

## Surprising Connections (you probably didn't know these)
- `parseLegacyWards()` --calls--> `values()`  [INFERRED]
  database/migrations/2026_05_27_000001_create_gp_wards_and_pole_number_formats.php → app/Enums/UserRole.php

## Communities

### Community 0 - "Community 0"
Cohesion: 0.02
Nodes (23): InventoryController, LoginController, InventoryController, PoleController, VendorController, InventoryImportFormatExport, InventoryAddStreetlightTest, MassAssignmentTest (+15 more)

### Community 1 - "Community 1"
Cohesion: 0.02
Nodes (25): StreetlightController, TaskController, ReadExcelFile, HomeController, JICRController, PerformanceDebugController, AnalyticsService, DashboardAnalyticsService (+17 more)

### Community 2 - "Community 2"
Cohesion: 0.03
Nodes (12): TasksController, InventoryService, MeetingManagementService, MeetingNoteHistory, Project, ProjectService, BaseService, SiteManagementService (+4 more)

### Community 3 - "Community 3"
Cohesion: 0.03
Nodes (16): StaffController, VendorController, RegisterController, ActivityLogController, SettingsController, LoginValidationTest, StaffImportTest, VendorValidationTest (+8 more)

### Community 4 - "Community 4"
Cohesion: 0.03
Nodes (13): ConveyanceController, PreviewController, CandidateController, ConvenienceController, StaffController, CandidateMail, Candidate, City (+5 more)

### Community 5 - "Community 5"
Cohesion: 0.03
Nodes (15): LoginController, OrganizationBrandingComposer, ExampleTest, SettingsFeatureTest, MeetingRepository, ProjectRepository, BaseRepository, ConveyanceService (+7 more)

### Community 6 - "Community 6"
Cohesion: 0.03
Nodes (15): DropdownController, ProjectController, SiteController, ProjectsController, RMSController, SiteController, UserFactory, PaginationTest (+7 more)

### Community 7 - "Community 7"
Cohesion: 0.04
Nodes (19): NotificationController, PerformanceController, QueueProcessorController, values(), QueryMappedExport, backfillSiteWards(), backfillTaskWards(), parseLegacyWards() (+11 more)

### Community 8 - "Community 8"
Cohesion: 0.04
Nodes (14): applyColumnFilterRaw(), applyColumnFilters(), buildColumnFilter(), columnCondition(), parseColumnFilters(), rawCondition(), downloadDataTableExport(), ProjectPoleController (+6 more)

### Community 9 - "Community 9"
Cohesion: 0.04
Nodes (7): WhiteboardController, User, DashboardSettingsService, RmsSettingsService, SettingsRepository, VendorEarningSetting, VendorEarningsSettingsService

### Community 10 - "Community 10"
Cohesion: 0.05
Nodes (6): DeviceController, StreetlightPoleImport, ProcessPoleImportChunk, ProcessTargetDeletionChunk, PoleImportJob, TargetDeletionJob

### Community 11 - "Community 11"
Cohesion: 0.05
Nodes (8): BackupDatabase, InventoryExport, TasksExport, CandidatesImport, InventroyStreetLight, SiteImport, StreetlightImport, InventroyStreetLightImportTest

### Community 12 - "Community 12"
Cohesion: 0.06
Nodes (5): MeetController, DiscussionPoint, DiscussionPointUpdates, FollowUp, Meet

### Community 13 - "Community 13"
Cohesion: 0.05
Nodes (15): indexExists(), up(), up(), up(), up(), up(), up(), up() (+7 more)

### Community 14 - "Community 14"
Cohesion: 0.08
Nodes (7): GPWardAndPoleFormatTest, PoleNumberFormatSettingsTest, RegeneratePoleNumbersJob, OrganizationSetting, PoleNumberFormat, PoleNumberFormatService, PoleNumberRegenerationBatch

### Community 15 - "Community 15"
Cohesion: 0.1
Nodes (4): DataTransformationService, BackupController, label(), options()

### Community 16 - "Community 16"
Cohesion: 0.11
Nodes (7): canManageProjects(), isAdmin(), isFieldRole(), label(), options(), ProjectPolicy, UserPolicy

### Community 17 - "Community 17"
Cohesion: 0.1
Nodes (6): RemoteApiHelper, ProcessRmsSyncChunk, DistrictCode, RmsPushLog, RmsSyncBatch, RmsSyncService

### Community 18 - "Community 18"
Cohesion: 0.15
Nodes (3): DateFormatter, DateFormatterPropertyTest, DateFormatterTest

### Community 19 - "Community 19"
Cohesion: 0.22
Nodes (1): CodeDocController

### Community 21 - "Community 21"
Cohesion: 0.18
Nodes (6): allowedTransitions(), canTransitionTo(), isTerminal(), label(), options(), TaskStateMachine

### Community 22 - "Community 22"
Cohesion: 0.17
Nodes (3): OrganizationSettingsService, SettingsAuditService, SettingsChangeLog

### Community 24 - "Community 24"
Cohesion: 0.17
Nodes (1): Streetlight

### Community 25 - "Community 25"
Cohesion: 0.23
Nodes (1): TargetImport

### Community 27 - "Community 27"
Cohesion: 0.2
Nodes (9): projects.partials.installed-poles-tab, projects.partials.project-poles-scripts, projects.partials.project-poles-styles, projects.partials.surveyed-poles-tab, projects.project_inventory, projects.project_site, projects.project_staff, projects.project_task (+1 more)

### Community 31 - "Community 31"
Cohesion: 0.25
Nodes (1): RooftopTaskStrategy

### Community 35 - "Community 35"
Cohesion: 0.29
Nodes (2): label(), options()

### Community 38 - "Community 38"
Cohesion: 0.29
Nodes (1): Pole

### Community 39 - "Community 39"
Cohesion: 0.33
Nodes (1): RooftopInventoryStrategy

### Community 40 - "Community 40"
Cohesion: 0.33
Nodes (1): RejectionLetter

### Community 41 - "Community 41"
Cohesion: 0.33
Nodes (1): AppointmentLetter

### Community 49 - "Community 49"
Cohesion: 0.4
Nodes (1): Stores

### Community 50 - "Community 50"
Cohesion: 0.33
Nodes (1): StoreProjectRequest

### Community 51 - "Community 51"
Cohesion: 0.33
Nodes (1): StoreTaskRequest

### Community 52 - "Community 52"
Cohesion: 0.33
Nodes (1): AssignTaskRequest

### Community 53 - "Community 53"
Cohesion: 0.4
Nodes (2): Role, RolePermissionSeeder

### Community 54 - "Community 54"
Cohesion: 0.4
Nodes (1): Inventory

### Community 55 - "Community 55"
Cohesion: 0.4
Nodes (1): StaffImportFormatExport

### Community 56 - "Community 56"
Cohesion: 0.4
Nodes (1): UpdateProjectRequest

### Community 57 - "Community 57"
Cohesion: 0.4
Nodes (4): dashboard.sections.inventory, dashboard.sections.meetings, dashboard.sections.performance, dashboard.sections.tada

### Community 58 - "Community 58"
Cohesion: 0.4
Nodes (1): jicr.show

### Community 59 - "Community 59"
Cohesion: 0.5
Nodes (3): staff.assignedTasks, staff.installedPoles, staff.surveyedPoles

### Community 60 - "Community 60"
Cohesion: 0.5
Nodes (3): performance.partials.engineer-card, performance.partials.manager-card, performance.partials.vendor-card

### Community 61 - "Community 61"
Cohesion: 0.4
Nodes (1): ApiLoggingTest

### Community 62 - "Community 62"
Cohesion: 0.5
Nodes (1): RepositoryServiceProvider

### Community 63 - "Community 63"
Cohesion: 0.5
Nodes (1): EventServiceProvider

### Community 64 - "Community 64"
Cohesion: 0.5
Nodes (1): InventoryImport

### Community 65 - "Community 65"
Cohesion: 0.5
Nodes (1): InventoryDispatchImport

### Community 66 - "Community 66"
Cohesion: 0.5
Nodes (1): SettingsPolicy

### Community 67 - "Community 67"
Cohesion: 0.5
Nodes (1): StreetlightPoleImportFormatExport

### Community 68 - "Community 68"
Cohesion: 0.5
Nodes (1): SyncPolesToRmsJob

### Community 69 - "Community 69"
Cohesion: 0.5
Nodes (1): Kernel

### Community 70 - "Community 70"
Cohesion: 0.5
Nodes (3): settings.partials.billing., settings.partials.billing.modals, settings.partials.billing.scripts

### Community 71 - "Community 71"
Cohesion: 0.5
Nodes (1): settings.partials.billing._empty-state

### Community 72 - "Community 72"
Cohesion: 0.5
Nodes (3): partials.footer, partials.header, partials.sidebar

### Community 98 - "Community 98"
Cohesion: 0.67
Nodes (1): UserSeeder

### Community 99 - "Community 99"
Cohesion: 0.67
Nodes (1): DatabaseSeeder

### Community 100 - "Community 100"
Cohesion: 0.67
Nodes (1): AuthServiceProvider

### Community 101 - "Community 101"
Cohesion: 0.67
Nodes (1): BroadcastServiceProvider

### Community 102 - "Community 102"
Cohesion: 0.67
Nodes (1): Journey

### Community 103 - "Community 103"
Cohesion: 0.67
Nodes (1): HotelExpense

### Community 104 - "Community 104"
Cohesion: 0.67
Nodes (1): dailyfare

### Community 105 - "Community 105"
Cohesion: 0.67
Nodes (1): Whiteboard

### Community 106 - "Community 106"
Cohesion: 0.67
Nodes (1): Handler

### Community 107 - "Community 107"
Cohesion: 0.67
Nodes (1): RedirectIfAuthenticated

### Community 108 - "Community 108"
Cohesion: 0.67
Nodes (1): RoleMiddleware

### Community 109 - "Community 109"
Cohesion: 0.67
Nodes (1): RestrictToMeetings

### Community 110 - "Community 110"
Cohesion: 0.67
Nodes (1): TrustHosts

### Community 111 - "Community 111"
Cohesion: 0.67
Nodes (1): RmsSyncController

### Community 112 - "Community 112"
Cohesion: 0.67
Nodes (1): VerificationController

### Community 113 - "Community 113"
Cohesion: 0.67
Nodes (1): PrintHelper

### Community 114 - "Community 114"
Cohesion: 0.67
Nodes (2): projects.project_task_rooftop, projects.project_task_streetlight

### Community 115 - "Community 115"
Cohesion: 0.67
Nodes (1): projects.partials.poles-unified-filters

### Community 116 - "Community 116"
Cohesion: 0.67
Nodes (1): ExampleTest

### Community 117 - "Community 117"
Cohesion: 1.0
Nodes (1): Permission

### Community 118 - "Community 118"
Cohesion: 1.0
Nodes (1): DashboardSetting

### Community 119 - "Community 119"
Cohesion: 1.0
Nodes (1): RmsSetting

### Community 120 - "Community 120"
Cohesion: 1.0
Nodes (1): Kernel

### Community 121 - "Community 121"
Cohesion: 1.0
Nodes (1): TrimStrings

### Community 122 - "Community 122"
Cohesion: 1.0
Nodes (1): TrustProxies

### Community 123 - "Community 123"
Cohesion: 1.0
Nodes (1): ValidateSignature

### Community 124 - "Community 124"
Cohesion: 1.0
Nodes (1): PreventRequestsDuringMaintenance

### Community 125 - "Community 125"
Cohesion: 1.0
Nodes (1): EncryptCookies

### Community 126 - "Community 126"
Cohesion: 1.0
Nodes (1): Controller

### Community 128 - "Community 128"
Cohesion: 1.0
Nodes (1): TestCase

## Knowledge Gaps
- **34 isolated node(s):** `Permission`, `DashboardSetting`, `RmsSetting`, `Kernel`, `TrimStrings` (+29 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **Thin community `Community 19`** (21 nodes): `CodeDocController.php`, `CodeDocController`, `.buildNavigationTree()`, `.buildStats()`, `.__construct()`, `.extractMethodData()`, `.formatFileSize()`, `.formatValue()`, `.getBusinessContext()`, `.getConstVisibility()`, `.getFileDocumentation()`, `.getFilesForCategory()`, `.getFileSummary()`, `.getRouteSummary()`, `.getVisibility()`, `.index()`, `.parseDocblock()`, `.resolveFilePath()`, `.resolveFQCN()`, `.show()`, `.slugify()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 24`** (12 nodes): `Streetlight.php`, `Streetlight`, `.getBlockCodeAttribute()`, `.getDistrictCodeAttribute()`, `.getPanchayatCodeAttribute()`, `.poles()`, `.project()`, `.scopeInstallationDone()`, `.scopeSurveyDone()`, `.scopeTotalPoles()`, `.siteWards()`, `.streetlightTasks()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 25`** (12 nodes): `TargetImport.php`, `TargetImport`, `.addError()`, `.collection()`, `.__construct()`, `.findPanchayatByFuzzyMatch()`, `.findUserByName()`, `.findUserByStrictName()`, `.getErrors()`, `.getImportedCount()`, `.getMultipleMatches()`, `.validateWardsExist()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 31`** (9 nodes): `RooftopTaskStrategy.php`, `RooftopTaskStrategy`, `.calculateProgress()`, `.getProgressMetrics()`, `.getRequiredFields()`, `.getTaskModel()`, `.getTaskType()`, `.prepareTaskData()`, `.validateTaskData()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 35`** (8 nodes): `ProjectType.php`, `description()`, `inventoryModelClass()`, `label()`, `options()`, `requiresAgreement()`, `siteModelClass()`, `taskModelClass()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 38`** (7 nodes): `Pole.php`, `Pole`, `.inventoryDispatches()`, `.rmsLogs()`, `.siteWard()`, `.task()`, `.vendor()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 39`** (7 nodes): `RooftopInventoryStrategy.php`, `RooftopInventoryStrategy`, `.calculateTotalValue()`, `.getAvailableStock()`, `.getModelClass()`, `.getValidationRules()`, `.prepareForStorage()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 40`** (6 nodes): `RejectionLetter.php`, `RejectionLetter`, `.attachments()`, `.__construct()`, `.content()`, `.envelope()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 41`** (6 nodes): `AppointmentLetter.php`, `AppointmentLetter`, `.attachments()`, `.__construct()`, `.content()`, `.envelope()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 49`** (6 nodes): `Stores.php`, `Stores`, `.inventory()`, `.project()`, `.storeIncharge()`, `.user()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 50`** (6 nodes): `StoreProjectRequest.php`, `StoreProjectRequest`, `.attributes()`, `.authorize()`, `.messages()`, `.rules()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 51`** (6 nodes): `StoreTaskRequest.php`, `StoreTaskRequest`, `.authorize()`, `.messages()`, `.prepareForValidation()`, `.rules()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 52`** (6 nodes): `AssignTaskRequest.php`, `AssignTaskRequest`, `.authorize()`, `.messages()`, `.rules()`, `.withValidator()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 53`** (5 nodes): `Role.php`, `RolePermissionSeeder.php`, `Role`, `RolePermissionSeeder`, `.run()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 54`** (5 nodes): `Inventory.php`, `Inventory`, `.project()`, `.site()`, `.store()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 55`** (5 nodes): `StaffImportFormatExport.php`, `StaffImportFormatExport`, `.collection()`, `.__construct()`, `.headings()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 56`** (5 nodes): `UpdateProjectRequest.php`, `UpdateProjectRequest`, `.authorize()`, `.messages()`, `.rules()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 58`** (5 nodes): `jicr.show`, `index.blade.php`, `temp.blade.php`, `show.blade.php`, `show_streetlight.blade.php`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 61`** (5 nodes): `ApiLoggingTest.php`, `ApiLoggingTest`, `.test_api_inventory_controller_resolves_activity_logger()`, `.test_api_site_controller_resolves_activity_logger()`, `.test_api_staff_controller_resolves_activity_logger()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 62`** (4 nodes): `RepositoryServiceProvider.php`, `RepositoryServiceProvider`, `.boot()`, `.register()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 63`** (4 nodes): `EventServiceProvider.php`, `EventServiceProvider`, `.boot()`, `.shouldDiscoverEvents()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 64`** (4 nodes): `InventoryImport.php`, `InventoryImport`, `.__construct()`, `.model()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 65`** (4 nodes): `InventoryDispatchImport.php`, `InventoryDispatchImport`, `.collection()`, `.__construct()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 66`** (4 nodes): `SettingsPolicy.php`, `SettingsPolicy`, `.update()`, `.viewAny()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 67`** (4 nodes): `StreetlightPoleImportFormatExport.php`, `StreetlightPoleImportFormatExport`, `.collection()`, `.headings()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 68`** (4 nodes): `SyncPolesToRmsJob.php`, `SyncPolesToRmsJob`, `.__construct()`, `.handle()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 69`** (4 nodes): `Kernel.php`, `Kernel`, `.commands()`, `.schedule()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 71`** (4 nodes): `categories-tab.blade.php`, `users-tab.blade.php`, `vehicles-tab.blade.php`, `settings.partials.billing._empty-state`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 98`** (3 nodes): `UserSeeder.php`, `UserSeeder`, `.run()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 99`** (3 nodes): `DatabaseSeeder.php`, `DatabaseSeeder`, `.run()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 100`** (3 nodes): `AuthServiceProvider.php`, `AuthServiceProvider`, `.boot()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 101`** (3 nodes): `BroadcastServiceProvider.php`, `BroadcastServiceProvider`, `.boot()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 102`** (3 nodes): `Journey.php`, `Journey`, `.tada()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 103`** (3 nodes): `HotelExpense.php`, `HotelExpense`, `.tada()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 104`** (3 nodes): `dailyfare.php`, `dailyfare`, `.tada()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 105`** (3 nodes): `Whiteboard.php`, `Whiteboard`, `.reviewMeeting()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 106`** (3 nodes): `Handler.php`, `Handler`, `.register()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 107`** (3 nodes): `RedirectIfAuthenticated.php`, `RedirectIfAuthenticated`, `.handle()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 108`** (3 nodes): `RoleMiddleware.php`, `RoleMiddleware`, `.handle()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 109`** (3 nodes): `RestrictToMeetings.php`, `RestrictToMeetings`, `.handle()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 110`** (3 nodes): `TrustHosts.php`, `TrustHosts`, `.hosts()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 111`** (3 nodes): `RmsSyncController.php`, `RmsSyncController`, `.show()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 112`** (3 nodes): `VerificationController.php`, `VerificationController`, `.__construct()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 113`** (3 nodes): `PrintHelper.php`, `PrintHelper`, `.__construct()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 114`** (3 nodes): `projects.project_task_rooftop`, `projects.project_task_streetlight`, `project_task.blade.php`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 115`** (3 nodes): `projects.partials.poles-unified-filters`, `installed-poles-tab.blade.php`, `surveyed-poles-tab.blade.php`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 116`** (3 nodes): `ExampleTest.php`, `ExampleTest`, `.test_that_true_is_true()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 117`** (2 nodes): `Permission.php`, `Permission`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 118`** (2 nodes): `DashboardSetting.php`, `DashboardSetting`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 119`** (2 nodes): `RmsSetting.php`, `RmsSetting`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 120`** (2 nodes): `Kernel.php`, `Kernel`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 121`** (2 nodes): `TrimStrings.php`, `TrimStrings`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 122`** (2 nodes): `TrustProxies.php`, `TrustProxies`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 123`** (2 nodes): `ValidateSignature.php`, `ValidateSignature`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 124`** (2 nodes): `PreventRequestsDuringMaintenance.php`, `PreventRequestsDuringMaintenance`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 125`** (2 nodes): `EncryptCookies.php`, `EncryptCookies`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 126`** (2 nodes): `Controller.php`, `Controller`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 128`** (2 nodes): `TestCase.php`, `TestCase`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Site` connect `Community 6` to `Community 0`, `Community 1`, `Community 2`, `Community 4`, `Community 7`, `Community 9`, `Community 13`, `Community 15`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._
- **Why does `InventroyStreetLightModel` connect `Community 0` to `Community 1`, `Community 2`, `Community 11`, `Community 15`?**
  _High betweenness centrality (0.022) - this node is a cross-community bridge._
- **Why does `ConvenienceController` connect `Community 4` to `Community 0`?**
  _High betweenness centrality (0.019) - this node is a cross-community bridge._
- **Are the 57 inferred relationships involving `StreetlightTask` (e.g. with `.getTasksByType()` and `.processRow()`) actually correct?**
  _`StreetlightTask` has 57 INFERRED edges - model-reasoned connections that need verification._
- **Are the 36 inferred relationships involving `InventoryDispatch` (e.g. with `.getInventoryAnalytics()` and `.show()`) actually correct?**
  _`InventoryDispatch` has 36 INFERRED edges - model-reasoned connections that need verification._
- **Are the 40 inferred relationships involving `values()` (e.g. with `parseLegacyWards()` and `.getAssignedProjects()`) actually correct?**
  _`values()` has 40 INFERRED edges - model-reasoned connections that need verification._
- **Are the 30 inferred relationships involving `Site` (e.g. with `.show()` and `.collectProjectData()`) actually correct?**
  _`Site` has 30 INFERRED edges - model-reasoned connections that need verification._