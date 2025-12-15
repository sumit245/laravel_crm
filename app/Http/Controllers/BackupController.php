<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Site;
use App\Models\Streetlight;
use App\Models\Task;
use App\Models\StreetlightTask;
use App\Models\Inventory;
use App\Models\InventroyStreetLightModel;
use App\Models\InventoryDispatch;
use App\Models\Stores;
use App\Models\Pole;
use App\Enums\ProjectType;
use App\Enums\UserRole;
use App\Services\Backup\DataTransformationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Helpers\ExcelHelper;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class BackupController extends Controller
{
    protected $transformer;

    public function __construct(DataTransformationService $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * Display the backup page
     */
    public function index()
    {
        $projects = Project::orderBy('project_name')->get();
        $backups = $this->getBackupFiles();

        return view('data_backup.backup', compact('projects', 'backups'));
    }

    /**
     * Create backup for selected projects
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'projects' => 'required|array|min:1',
            'projects.*' => 'exists:projects,id',
            'user_type' => 'nullable|in:all,staff,vendors',
            'format' => 'required|in:excel,csv,sql',
        ]);

        try {
            // Increase memory limit and execution time for backup operations
            ini_set('memory_limit', '2048M');
            ini_set('max_execution_time', '600'); // 10 minutes

            $projects = Project::whereIn('id', $validated['projects'])->get();
            $userType = $validated['user_type'] ?? 'all';
            $format = $validated['format'];

            // Create backup directory if it doesn't exist
            $backupPath = storage_path('app/backups');
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }

            $files = [];

            // Create backup for each project
            foreach ($projects as $project) {
                if ($format === 'sql') {
                    $filename = $this->createSqlBackup($project);
                } else {
                    $filename = $this->createExcelCsvBackup($project, $userType, $format);
                }

                if ($filename) {
                    $files[] = $filename;
                }

                // Clear memory after each project
                gc_collect_cycles();
            }

            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully',
                'files' => $files,
            ]);
        } catch (\Exception $e) {
            \Log::error('Backup creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function download($filename)
    {
        $filePath = storage_path('app/backups/' . basename($filename));

        if (!File::exists($filePath)) {
            abort(404, 'Backup file not found');
        }

        return response()->download($filePath);
    }

    /**
     * Delete backup file
     */
    public function delete($filename)
    {
        $filePath = storage_path('app/backups/' . basename($filename));

        if (!File::exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        File::delete($filePath);

        return response()->json([
            'success' => true,
            'message' => 'Backup deleted successfully',
        ]);
    }

    /**
     * Get list of backup files
     */
    private function getBackupFiles()
    {
        $backupPath = storage_path('app/backups');

        if (!File::exists($backupPath)) {
            return [];
        }

        $files = File::files($backupPath);
        $backups = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $extension = $file->getExtension();

            $backups[] = [
                'name' => $filename,
                'size' => $this->formatBytes($file->getSize()),
                'sizeBytes' => $file->getSize(),
                'date' => Carbon::createFromTimestamp($file->getMTime())->format('Y-m-d'),
                'type' => $extension,
            ];
        }

        // Sort by date descending
        usort($backups, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $backups;
    }

    /**
     * Create SQL backup for a project
     * Note: SQL format creates a full database backup (project-specific SQL filtering is complex)
     */
    private function createSqlBackup($project)
    {
        $db = config('database.connections.mysql');
        $filename = 'backup_' . $this->sanitizeFilename($project->project_name) . '_' . $project->id . '_' . Carbon::now()->format('Y_m_d_His') . '.sql';

        // Ensure backup directory exists
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filePath = $backupDir . '/' . $filename;

        $command = sprintf(
            'mysqldump -u%s -p%s -h%s %s > %s',
            escapeshellarg($db['username']),
            escapeshellarg($db['password']),
            escapeshellarg($db['host']),
            escapeshellarg($db['database']),
            escapeshellarg($filePath)
        );

        exec($command, $output, $result);

        if ($result === 0 && File::exists($filePath)) {
            return $filename;
        }

        return null;
    }

    /**
     * Create Excel/CSV backup for a project
     */
    private function createExcelCsvBackup($project, $userType, $format)
    {
        $projectType = $project->project_type;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $filename = 'backup_' . $this->sanitizeFilename($project->project_name) . '_' . $project->id . '_' . Carbon::now()->format('Y_m_d_His') . '.' . $extension;

        // Ensure backup directory exists
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filePath = $backupDir . '/' . $filename;

        // Collect all data for the project
        $data = $this->collectProjectData($project, $userType);

        if ($format === 'csv') {
            $this->exportToCsv($data, $filePath, $projectType);
        } else {
            $this->exportToExcel($data, $filePath, $projectType);
        }

        // Verify file was created
        if (File::exists($filePath)) {
            return $filename;
        }

        return null;
    }

    /**
     * Collect all project data
     */
    private function collectProjectData($project, $userType)
    {
        $projectType = $project->project_type;
        $data = [];

        // Project details
        $data['project'] = [$this->transformer->transformProject($project)];

        // Staff data - process in smaller chunks to reduce memory usage
        $userQuery = $this->getProjectUsers($project, $userType);
        $staffData = [];
        $userQuery->chunk(100, function ($userChunk) use (&$staffData) {
            foreach ($userChunk as $user) {
                $staffData[] = $this->transformer->transformUser($user);
            }
            unset($userChunk);
            gc_collect_cycles();
        });
        $data['staff'] = $staffData;
        unset($staffData);

        if ($projectType == ProjectType::STREETLIGHT->value) {
            // Streetlight-specific data - process in smaller chunks
            $streetlightSitesData = [];
            Streetlight::where('project_id', $project->id)
                ->chunk(100, function ($sites) use (&$streetlightSitesData) {
                    foreach ($sites as $site) {
                        $streetlightSitesData[] = $this->transformer->transformStreetlightSite($site);
                    }
                    unset($sites);
                    gc_collect_cycles();
                });
            $data['streetlight_sites'] = $streetlightSitesData;
            unset($streetlightSitesData);

            // Get targets in smaller chunks to avoid memory issues
            $targetsData = [];
            StreetlightTask::where('project_id', $project->id)
                ->chunk(100, function ($tasks) use (&$targetsData, $projectType) {
                    // Load relationships for this chunk
                    $tasks->load(['site', 'engineer', 'vendor', 'manager']);
                    foreach ($tasks as $task) {
                        $targetsData[] = $this->transformer->transformTask($task, $projectType);
                    }
                    unset($tasks);
                    gc_collect_cycles();
                });
            $data['targets'] = $targetsData;
            unset($targetsData);

            // Get poles in smaller chunks to avoid memory issues
            $polesData = [];
            Pole::whereHas('task', function ($query) use ($project) {
                $query->where('project_id', $project->id);
            })->chunk(100, function ($poles) use (&$polesData) {
                foreach ($poles as $pole) {
                    $polesData[] = $this->transformer->transformPole($pole);
                }
                unset($poles);
                gc_collect_cycles();
            });
            $data['poles'] = $polesData;
            unset($polesData);

            // Store inventory - process in smaller chunks
            $storesData = [];
            Stores::where('project_id', $project->id)
                ->chunk(100, function ($stores) use (&$storesData) {
                    foreach ($stores as $store) {
                        $storesData[] = [
                            'id' => $store->id,
                            'store_name' => $store->store_name ?? 'N/A',
                            'address' => $store->address ?? 'N/A',
                        ];
                    }
                    unset($stores);
                    gc_collect_cycles();
                });
            $data['stores'] = $storesData;
            unset($storesData);

            // Store inventory - process in smaller chunks
            $storeInventoryData = [];
            InventroyStreetLightModel::where('project_id', $project->id)
                ->chunk(100, function ($items) use (&$storeInventoryData) {
                    // Load relationships for this chunk
                    $items->load('store');
                    foreach ($items as $item) {
                        $storeInventoryData[] = [
                            'store_name' => ($item->store && isset($item->store->store_name)) ? $item->store->store_name : 'N/A',
                            'item_code' => $item->item_code ?? 'N/A',
                            'item' => $item->item ?? 'N/A',
                            'manufacturer' => $item->manufacturer ?? 'N/A',
                            'make' => $item->make ?? 'N/A',
                            'model' => $item->model ?? 'N/A',
                            'serial_number' => $item->serial_number ?? 'N/A',
                            'quantity' => $item->quantity ?? 0,
                            'rate' => $item->rate ?? 0,
                            'total_value' => $item->total_value ?? 0,
                            'received_date' => $this->transformer->formatDateField($item->received_date ?? null),
                        ];
                    }
                    unset($items);
                    gc_collect_cycles();
                });
            $data['store_inventory'] = $storeInventoryData;
            unset($storeInventoryData);

            // Vendor data (users with role 3) - get vendors assigned to tasks in this project
            $vendorIds = StreetlightTask::where('project_id', $project->id)
                ->whereNotNull('vendor_id')
                ->distinct()
                ->pluck('vendor_id')
                ->toArray();

            $vendorsData = [];
            if (!empty($vendorIds)) {
                User::where('role', UserRole::VENDOR->value)
                    ->whereIn('id', $vendorIds)
                    ->chunk(100, function ($users) use (&$vendorsData) {
                        foreach ($users as $user) {
                            $vendorsData[] = $this->transformer->transformUser($user);
                        }
                        unset($users);
                        gc_collect_cycles();
                    });
            }
            $data['vendors'] = $vendorsData;
            unset($vendorsData);
        } else {
            // Rooftop-specific data - process sites in smaller chunks
            $sitesData = [];
            Site::where('project_id', $project->id)
                ->chunk(100, function ($sites) use (&$sitesData) {
                    foreach ($sites as $site) {
                        $sitesData[] = $this->transformer->transformSite($site);
                    }
                    unset($sites);
                    gc_collect_cycles();
                });
            $data['sites'] = $sitesData;
            unset($sitesData);

            // Get tasks in smaller chunks to avoid memory issues
            $tasksData = [];
            Task::where('project_id', $project->id)
                ->chunk(100, function ($tasks) use (&$tasksData, $projectType) {
                    // Load relationships for this chunk
                    $tasks->load(['site', 'engineer', 'vendor', 'manager']);
                    foreach ($tasks as $task) {
                        $tasksData[] = $this->transformer->transformTask($task, $projectType);
                    }
                    unset($tasks);
                    gc_collect_cycles();
                });
            $data['tasks'] = $tasksData;
            unset($tasksData);

            // Inventory used (dispatched) - process in smaller chunks
            $inventoryUsedData = [];
            InventoryDispatch::where('project_id', $project->id)
                ->chunk(100, function ($dispatches) use (&$inventoryUsedData) {
                    // Load relationships for this chunk
                    $dispatches->load(['vendor', 'store']);
                    foreach ($dispatches as $dispatch) {
                        $vendorName = 'N/A';
                        if ($dispatch->vendor) {
                            if (isset($dispatch->vendor->name)) {
                                $vendorName = $dispatch->vendor->name;
                            } else {
                                $vendorName = ($dispatch->vendor->firstName ?? '') . ' ' . ($dispatch->vendor->lastName ?? '');
                            }
                        }

                        $inventoryUsedData[] = [
                            'item_code' => $dispatch->item_code ?? 'N/A',
                            'item' => $dispatch->item ?? 'N/A',
                            'vendor_name' => $vendorName,
                            'store_name' => ($dispatch->store && isset($dispatch->store->store_name)) ? $dispatch->store->store_name : 'N/A',
                            'total_quantity' => $dispatch->total_quantity ?? 0,
                            'total_value' => $dispatch->total_value ?? 0,
                            'isDispatched' => isset($dispatch->isDispatched) ? ($dispatch->isDispatched ? 'Yes' : 'No') : 'N/A',
                            'is_consumed' => isset($dispatch->is_consumed) ? ($dispatch->is_consumed ? 'Yes' : 'No') : 'N/A',
                            'dispatch_date' => $this->transformer->formatDateField($dispatch->dispatch_date ?? null),
                        ];
                    }
                    unset($dispatches);
                    gc_collect_cycles();
                });
            $data['inventory_used'] = $inventoryUsedData;
            unset($inventoryUsedData);

            // Inventory in stock - stores (smaller chunks)
            $storesData = [];
            Stores::where('project_id', $project->id)
                ->chunk(100, function ($stores) use (&$storesData) {
                    foreach ($stores as $store) {
                        $storesData[] = [
                            'id' => $store->id,
                            'store_name' => $store->store_name ?? 'N/A',
                            'address' => $store->address ?? 'N/A',
                        ];
                    }
                    unset($stores);
                    gc_collect_cycles();
                });
            $data['stores'] = $storesData;
            unset($storesData);

            // Inventory stock - process in smaller chunks
            $inventoryStockData = [];
            Inventory::where('project_id', $project->id)
                ->chunk(100, function ($items) use (&$inventoryStockData) {
                    // Load relationships for this chunk
                    $items->load('store');
                    foreach ($items as $item) {
                        $inventoryStockData[] = [
                            'store_name' => ($item->store && isset($item->store->store_name)) ? $item->store->store_name : 'N/A',
                            'productName' => $item->productName ?? 'N/A',
                            'category' => $item->category ?? 'N/A',
                            'sub_category' => $item->sub_category ?? 'N/A',
                            'brand' => $item->brand ?? 'N/A',
                            'unit' => $item->unit ?? 'N/A',
                            'initialQuantity' => $item->initialQuantity ?? 0,
                            'quantityStock' => $item->quantityStock ?? 0,
                            'rate' => $item->rate ?? 0,
                            'total' => $item->total ?? 0,
                        ];
                    }
                    unset($items);
                    gc_collect_cycles();
                });
            $data['inventory_stock'] = $inventoryStockData;
            unset($inventoryStockData);

            // Sites done (completed installations) - process in smaller chunks
            // For rooftop projects, sites are considered done if they have a commissioning_date
            $sitesDoneData = [];
            Site::where('project_id', $project->id)
                ->whereNotNull('commissioning_date')
                ->chunk(100, function ($sites) use (&$sitesDoneData) {
                    foreach ($sites as $site) {
                        $sitesDoneData[] = $this->transformer->transformSite($site);
                    }
                    unset($sites);
                    gc_collect_cycles();
                });
            $data['sites_done'] = $sitesDoneData;
            unset($sitesDoneData);
        }

        // Final memory cleanup before returning
        gc_collect_cycles();

        return $data;
    }

    /**
     * Get project users based on user type filter
     */
    private function getProjectUsers($project, $userType)
    {
        // Get users assigned to project via project_user pivot table
        $projectUserIds = DB::table('project_user')
            ->where('project_id', $project->id)
            ->pluck('user_id')
            ->toArray();

        // Also get users assigned via tasks
        $taskUserIds = [];
        if ($project->project_type == ProjectType::STREETLIGHT->value) {
            $engineerIds = StreetlightTask::where('project_id', $project->id)
                ->whereNotNull('engineer_id')
                ->distinct()
                ->pluck('engineer_id')
                ->toArray();

            $vendorIds = StreetlightTask::where('project_id', $project->id)
                ->whereNotNull('vendor_id')
                ->distinct()
                ->pluck('vendor_id')
                ->toArray();

            $managerIds = StreetlightTask::where('project_id', $project->id)
                ->whereNotNull('manager_id')
                ->distinct()
                ->pluck('manager_id')
                ->toArray();

            $taskUserIds = array_merge($engineerIds, $vendorIds, $managerIds);
        } else {
            $engineerIds = Task::where('project_id', $project->id)
                ->whereNotNull('engineer_id')
                ->distinct()
                ->pluck('engineer_id')
                ->toArray();

            $vendorIds = Task::where('project_id', $project->id)
                ->whereNotNull('vendor_id')
                ->distinct()
                ->pluck('vendor_id')
                ->toArray();

            $managerIds = Task::where('project_id', $project->id)
                ->whereNotNull('manager_id')
                ->distinct()
                ->pluck('manager_id')
                ->toArray();

            $taskUserIds = array_merge($engineerIds, $vendorIds, $managerIds);
        }

        $allUserIds = array_unique(array_merge($projectUserIds, $taskUserIds));

        if (empty($allUserIds)) {
            return collect([]);
        }

        $query = User::whereIn('id', $allUserIds);

        if ($userType === 'staff') {
            $query->where('role', '!=', UserRole::VENDOR->value);
        } elseif ($userType === 'vendors') {
            $query->where('role', UserRole::VENDOR->value);
        }

        // Return collection directly (don't call get() here, let chunk handle it)
        return $query;
    }

    /**
     * Export data to Excel
     */
    private function exportToExcel($data, $filePath, $projectType)
    {
        // Clear any lingering memory before export
        gc_collect_cycles();
        $sheets = [];

        // Project Details
        if (!empty($data['project'])) {
            $sheets['Project Details'] = $data['project'];
        }

        if ($projectType == ProjectType::STREETLIGHT->value) {
            // Streetlight-specific sheets
            if (!empty($data['streetlight_sites'])) {
                $sheets['Streetlight Sites'] = $data['streetlight_sites'];
            }
            if (!empty($data['stores'])) {
                $sheets['Stores'] = $data['stores'];
            }
            if (!empty($data['store_inventory'])) {
                $sheets['Store Inventory'] = $data['store_inventory'];
            }
            if (!empty($data['staff'])) {
                $sheets['Staff'] = $data['staff'];
            }
            if (!empty($data['vendors'])) {
                $sheets['Vendors'] = $data['vendors'];
            }
            if (!empty($data['targets'])) {
                $sheets['Targets'] = $data['targets'];
            }
            if (!empty($data['poles'])) {
                $sheets['Installations'] = $data['poles'];
            }
        } else {
            // Rooftop-specific sheets
            if (!empty($data['sites'])) {
                $sheets['Sites'] = $data['sites'];
            }
            if (!empty($data['staff'])) {
                $sheets['Staff'] = $data['staff'];
            }
            if (!empty($data['tasks'])) {
                $sheets['Tasks'] = $data['tasks'];
            }
            if (!empty($data['inventory_used'])) {
                $sheets['Inventory Used'] = $data['inventory_used'];
            }
            if (!empty($data['stores'])) {
                $sheets['Stores'] = $data['stores'];
            }
            if (!empty($data['inventory_stock'])) {
                $sheets['Inventory in Stock'] = $data['inventory_stock'];
            }
            if (!empty($data['sites_done'])) {
                $sheets['Sites Done'] = $data['sites_done'];
            }
        }

        // Use ExcelHelper's MultiSheetExport class
        // Ensure the class is loaded (it's in the same file as ExcelHelper)
        if (!class_exists(\App\Helpers\MultiSheetExport::class)) {
            require_once app_path('Helpers/ExcelHelper.php');
        }
        $export = new \App\Helpers\MultiSheetExport($sheets);
        $filename = basename($filePath);
        $relativePath = 'backups/' . $filename;

        // Store the file using Laravel Excel
        Excel::store($export, $relativePath, 'local');

        // Clear export object from memory
        unset($export);
        gc_collect_cycles();

        // Verify file was created at the expected path
        $storedPath = storage_path('app/' . $relativePath);
        if (!File::exists($storedPath)) {
            throw new \Exception('Failed to create Excel file at ' . $storedPath);
        }

        // Ensure the file is at the correct location (filePath should match storedPath)
        // If they're different, move/copy the file
        if ($storedPath !== $filePath) {
            if (File::exists($storedPath)) {
                // Move the file to the expected location
                File::move($storedPath, $filePath);
            }
        }

        // Final memory cleanup
        gc_collect_cycles();
    }

    /**
     * Export data to CSV (single file with multiple sections)
     */
    private function exportToCsv($data, $filePath, $projectType)
    {
        $handle = fopen($filePath, 'w');

        // Write Project Details
        if (!empty($data['project'])) {
            fputcsv($handle, ['=== PROJECT DETAILS ===']);
            fputcsv($handle, array_keys($data['project'][0]));
            foreach ($data['project'] as $row) {
                fputcsv($handle, array_values($row));
            }
            fputcsv($handle, []); // Empty line
        }

        // Write other sections based on project type
        if ($projectType == ProjectType::STREETLIGHT->value) {
            $sections = [
                'streetlight_sites' => 'Streetlight Sites',
                'stores' => 'Stores',
                'store_inventory' => 'Store Inventory',
                'staff' => 'Staff',
                'vendors' => 'Vendors',
                'targets' => 'Targets',
                'poles' => 'Installations',
            ];
        } else {
            $sections = [
                'sites' => 'Sites',
                'staff' => 'Staff',
                'tasks' => 'Tasks',
                'inventory_used' => 'Inventory Used',
                'stores' => 'Stores',
                'inventory_stock' => 'Inventory in Stock',
                'sites_done' => 'Sites Done',
            ];
        }

        foreach ($sections as $key => $title) {
            if (!empty($data[$key])) {
                fputcsv($handle, ['=== ' . strtoupper($title) . ' ===']);
                fputcsv($handle, array_keys($data[$key][0]));
                foreach ($data[$key] as $row) {
                    fputcsv($handle, array_values($row));
                }
                fputcsv($handle, []); // Empty line
            }
        }

        fclose($handle);
    }

    /**
     * Sanitize filename
     */
    private function sanitizeFilename($filename)
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
    }

    /**
     * Format bytes to human-readable size
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
