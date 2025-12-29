<?php

namespace App\Imports;

use App\Enums\UserRole;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class TargetImport implements ToCollection, WithHeadingRow
{
    protected $projectId;
    protected $currentUser;
    protected array $errors = [];
    protected array $multipleMatches = []; // Track rows with multiple user matches
    protected int $importedCount = 0;

    // Constructor to accept project ID and current user
    public function __construct($projectId, $currentUser = null)
    {
        $this->projectId = $projectId;
        $this->currentUser = $currentUser ?? auth()->user();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                // Skip empty rows
                if (empty($row['panchayat']) && empty($row['engineer_name']) && empty($row['vendor_name'])) {
                    continue;
                }

                $panchayatName = trim($row['panchayat'] ?? '');
                $engineerName = trim($row['engineer_name'] ?? '');
                $vendorName = trim($row['vendor_name'] ?? '');
                $assignedDate = trim($row['assigned_date'] ?? '');
                $endDate = trim($row['end_date'] ?? '');
                $wards = trim($row['wards'] ?? '');

                // Validate required fields
                if (empty($panchayatName)) {
                    $this->addError($index + 2, $panchayatName, $engineerName, $vendorName, $wards, 'Panchayat is required');
                    continue;
                }

                // Find panchayat using fuzzy matching
                $streetlight = $this->findPanchayatByFuzzyMatch($panchayatName, $this->projectId);
                if (!$streetlight) {
                    $this->addError($index + 2, $panchayatName, $engineerName, $vendorName, $wards, "we could not find \"{$panchayatName}\" as a panchayat");
                    continue;
                }

                // Validate wards exist in the matched panchayat
                if (!empty($wards) && !$this->validateWardsExist($wards, $streetlight)) {
                    $this->addError($index + 2, $panchayatName, $engineerName, $vendorName, $wards, "One or more wards do not exist in panchayat \"{$panchayatName}\"");
                    continue;
                }

                // Find engineer by name using strict matching
                $engineerResult = $this->findUserByStrictName($engineerName, UserRole::SITE_ENGINEER);
                if (is_array($engineerResult)) {
                    // Multiple matches found - store for later handling
                    $this->multipleMatches[] = [
                        'row' => $index + 2,
                        'type' => 'engineer',
                        'name' => $engineerName,
                        'panchayat' => $panchayatName,
                        'vendor_name' => $vendorName,
                        'wards' => $wards,
                        'matches' => $engineerResult
                    ];
                    $this->addError($index + 2, $panchayatName, $engineerName, $vendorName, $wards, "Multiple engineers found with name \"{$engineerName}\". Please be more specific.");
                    continue;
                } elseif (!$engineerResult) {
                    $this->addError($index + 2, $panchayatName, $engineerName, $vendorName, $wards, "Engineer does not exist");
                    continue;
                }
                $engineerId = $engineerResult;

                // Find vendor by name using strict matching
                $vendorResult = $this->findUserByStrictName($vendorName, UserRole::VENDOR);
                if (is_array($vendorResult)) {
                    // Multiple matches found - store for later handling
                    $this->multipleMatches[] = [
                        'row' => $index + 2,
                        'type' => 'vendor',
                        'name' => $vendorName,
                        'panchayat' => $panchayatName,
                        'engineer_name' => $engineerName,
                        'wards' => $wards,
                        'matches' => $vendorResult
                    ];
                    $this->addError($index + 2, $panchayatName, $engineerName, $vendorName, $wards, "Multiple vendors found with name \"{$vendorName}\". Please be more specific.");
                    continue;
                } elseif (!$vendorResult) {
                    $this->addError($index + 2, $panchayatName, $engineerName, $vendorName, $wards, "Vendor does not exist");
                    continue;
                }
                $vendorId = $vendorResult;

                // Check if task already exists for this site
                $existingTask = StreetlightTask::where('site_id', $streetlight->id)
                    ->where('project_id', $this->projectId)
                    ->first();

                if ($existingTask) {
                    $this->addError($index + 2, $panchayatName, $engineerName, $vendorName, $wards, 'Task already exists for this panchayat');
                    continue;
                }

                // Parse dates
                $startDate = !empty($assignedDate) ? date('Y-m-d', strtotime($assignedDate)) : now()->toDateString();
                $endDateParsed = !empty($endDate) ? date('Y-m-d', strtotime($endDate)) : null;

                // Create the task
                StreetlightTask::create([
                    'project_id' => $this->projectId,
                    'site_id' => $streetlight->id,
                    'engineer_id' => $engineerId,
                    'vendor_id' => $vendorId,
                    'start_date' => $startDate,
                    'end_date' => $endDateParsed,
                    'status' => 'Pending',
                ]);

                $this->importedCount++;
            } catch (\Exception $e) {
                $this->addError(
                    $index + 2,
                    $row['panchayat'] ?? '',
                    $row['engineer_name'] ?? '',
                    $row['vendor_name'] ?? '',
                    $row['wards'] ?? '',
                    'Error: ' . $e->getMessage()
                );
                Log::error("TargetImport row " . ($index + 2) . " error: " . $e->getMessage(), ['row' => $row ?? []]);
            }
        }
    }

    /**
     * Find panchayat using exact match first, then fuzzy matching with 90% similarity threshold
     */
    private function findPanchayatByFuzzyMatch(string $panchayatName, int $projectId): ?Streetlight
    {
        $panchayatName = trim($panchayatName);
        if (empty($panchayatName)) {
            return null;
        }

        // Try exact match first (case-insensitive, trimmed)
        $exactMatch = Streetlight::where('project_id', $projectId)
            ->whereRaw('TRIM(LOWER(panchayat)) = ?', [strtolower($panchayatName)])
            ->first();

        if ($exactMatch) {
            return $exactMatch;
        }

        // If no exact match, try fuzzy matching
        $panchayats = Streetlight::where('project_id', $projectId)
            ->distinct()
            ->pluck('panchayat')
            ->unique()
            ->filter();

        if ($panchayats->isEmpty()) {
            return null;
        }

        $bestMatch = null;
        $bestSimilarity = 0;
        $searchPanchayatLower = strtolower($panchayatName);

        foreach ($panchayats as $dbPanchayat) {
            $dbPanchayatLower = strtolower(trim($dbPanchayat));

            similar_text($searchPanchayatLower, $dbPanchayatLower, $percent);

            if ($percent >= 90 && $percent > $bestSimilarity) {
                $bestSimilarity = $percent;
                $bestMatch = $dbPanchayat;
            }
        }

        if ($bestMatch) {
            return Streetlight::where('project_id', $projectId)
                ->where('panchayat', $bestMatch)
                ->first();
        }

        return null;
    }

    /**
     * Find user by name and role(s)
     * @param string|null $name The user's full name
     * @param UserRole|array<UserRole> $role Single role or array of roles to search
     * @return int|null The user ID if found, null otherwise
     */
    private function findUserByName(?string $name, UserRole|array $role): ?int
    {
        if (empty($name)) {
            return null;
        }

        // Normalize role to array
        $roles = is_array($role) ? $role : [$role];
        $roleValues = array_map(fn($r) => $r->value, $roles);

        // Remove common prefixes
        $cleanName = trim(preg_replace('/^(Mr\.?|Mrs\.?|Miss|Ms\.?|Dr\.?|Prof\.?)\s*/i', '', $name));

        if (empty($cleanName)) {
            return null;
        }

        // Split name into parts
        $nameParts = array_filter(explode(' ', $cleanName));
        if (empty($nameParts)) {
            return null;
        }

        $firstName = trim($nameParts[0]);
        $lastName = count($nameParts) > 1 ? trim(implode(' ', array_slice($nameParts, 1))) : null;

        // Try exact match first (case-insensitive, trimmed)
        if ($lastName) {
            $user = User::whereIn('role', $roleValues)
                ->whereRaw('TRIM(LOWER(firstName)) = ?', [strtolower($firstName)])
                ->whereRaw('TRIM(LOWER(lastName)) = ?', [strtolower($lastName)])
                ->first();
        } else {
            $user = User::whereIn('role', $roleValues)
                ->whereRaw('TRIM(LOWER(firstName)) = ?', [strtolower($firstName)])
                ->first();
        }

        // If not found, try LIKE match with first name and last name
        if (!$user && $lastName) {
            $user = User::whereIn('role', $roleValues)
                ->where('firstName', 'like', $firstName . '%')
                ->where(function ($q) use ($lastName) {
                    $q->where('lastName', 'like', $lastName . '%')
                        ->orWhere('lastName', 'like', '%' . $lastName . '%');
                })
                ->first();
        }

        // If still not found, try matching with just first name
        if (!$user && $firstName) {
            $user = User::whereIn('role', $roleValues)
                ->where(function ($q) use ($firstName) {
                    $q->where('firstName', 'like', $firstName . '%')
                        ->orWhere('name', 'like', '%' . $firstName . '%');
                })
                ->first();
        }

        return $user ? $user->id : null;
    }

    /**
     * Find user by name using strict step-by-step matching
     * Step 1: Split name to firstname and lastname
     * Step 2: Search for firstname (exact match, case-insensitive) -> resultset1
     * Step 3: Filter resultset1 by lastname (exact match, case-insensitive) -> resultset3
     * Step 4: Filter resultset3 by project assignment -> resultset4
     * Step 5: Filter resultset4 by role -> resultset5
     * Step 6: Filter resultset5 by manager_id (if project manager) -> final result
     * 
     * @param string|null $name The user's full name
     * @param UserRole $role The role to search for
     * @return int|array|null The user ID if single match found, array of matches if multiple found (for admin), null if not found
     */
    private function findUserByStrictName(?string $name, UserRole $role): int|array|null
    {
        if (empty($name)) {
            return null;
        }

        // Step 1: Split name into firstname and lastname
        $cleanName = trim(preg_replace('/^(Mr\.?|Mrs\.?|Miss|Ms\.?|Dr\.?|Prof\.?)\s*/i', '', $name));
        if (empty($cleanName)) {
            return null;
        }

        $nameParts = array_filter(explode(' ', $cleanName));
        if (empty($nameParts)) {
            return null;
        }

        $firstName = trim($nameParts[0]);
        $lastName = count($nameParts) > 1 ? trim(implode(' ', array_slice($nameParts, 1))) : null;

        if (empty($firstName)) {
            return null;
        }

        // Step 2: Search for firstname (exact match, case-insensitive) -> resultset1
        $resultset1 = User::whereRaw('TRIM(LOWER(firstName)) = ?', [strtolower($firstName)])->get();

        if ($resultset1->isEmpty()) {
            return null;
        }

        // Step 3: Filter resultset1 by lastname (exact match, case-insensitive) -> resultset3
        // If lastName is provided in import, must match exactly. If not provided, accept all from resultset1.
        $resultset3 = $resultset1;
        if ($lastName && !empty(trim($lastName))) {
            $resultset3 = $resultset1->filter(function ($user) use ($lastName) {
                $userLastName = trim($user->lastName ?? '');
                return !empty($userLastName) && strtolower($userLastName) === strtolower($lastName);
            });
        }

        if ($resultset3->isEmpty()) {
            return null;
        }

        $userIds = $resultset3->pluck('id')->toArray();

        // Step 4: Filter resultset3 by project assignment (users in project_user pivot table) -> resultset4
        $resultset4 = User::whereIn('id', $userIds)
            ->whereHas('projects', function ($query) {
                $query->where('projects.id', $this->projectId);
            })
            ->get();

        if ($resultset4->isEmpty()) {
            return null;
        }

        $userIds = $resultset4->pluck('id')->toArray();

        // Step 5: Filter resultset4 by role (SITE_ENGINEER for engineers, VENDOR for vendors) -> resultset5
        // For vendors: Check main role (users.role) as pivot role might not be accurate for vendors
        // For engineers: Check pivot role (project_user.role) as engineers can have different roles assigned to projects
        if ($role === UserRole::VENDOR) {
            $resultset5 = User::whereIn('id', $userIds)
                ->where('role', $role->value)
                ->whereHas('projects', function ($query) {
                    $query->where('projects.id', $this->projectId);
                })
                ->get();
        } else {
            // For engineers: Check the pivot role in project_user table (project-specific role assignment)
            $resultset5 = User::whereIn('id', $userIds)
                ->whereHas('projects', function ($query) use ($role) {
                    $query->where('projects.id', $this->projectId)
                        ->where('project_user.role', $role->value);
                })
                ->get();
        }

        if ($resultset5->isEmpty()) {
            return null;
        }

        // Step 6: Filter resultset5 by manager_id (if project manager, admin can assign to anyone) -> final result
        $isAdmin = $this->currentUser && $this->currentUser->role === UserRole::ADMIN->value;
        $isProjectManager = $this->currentUser && $this->currentUser->role === UserRole::PROJECT_MANAGER->value;

        if ($isProjectManager) {
            // Project Manager: Only match users assigned to them (manager_id match)
            $finalResults = $resultset5->filter(function ($user) {
                return $user->manager_id === $this->currentUser->id;
            });
        } else {
            // Admin: Can assign to anyone, so include all results
            $finalResults = $resultset5;
        }

        if ($finalResults->isEmpty()) {
            return null;
        }

        // If admin and multiple matches found, return array of matches for confirmation
        if ($isAdmin && $finalResults->count() > 1) {
            return $finalResults->map(function ($user) {
                $projectManager = $user->projectManager;
                return [
                    'id' => $user->id,
                    'name' => trim($user->firstName . ' ' . $user->lastName),
                    'first_name' => $user->firstName,
                    'last_name' => $user->lastName,
                    'email' => $user->email,
                    'contact_no' => $user->contactNo ?? '',
                    'project_manager' => $projectManager ? trim($projectManager->firstName . ' ' . $projectManager->lastName) : 'N/A',
                    'project_manager_id' => $user->manager_id
                ];
            })->values()->toArray();
        }

        // Single match found - return the user ID
        return $finalResults->first()->id;
    }

    /**
     * Validate that wards exist in the matched streetlight
     */
    private function validateWardsExist(string $wards, Streetlight $streetlight): bool
    {
        if (empty($wards) || empty($streetlight->ward)) {
            return true; // If no wards specified or no wards in DB, allow
        }

        // Parse wards from input (handle formats like "1,2,3" or "Ward 1, Ward 2, Ward 3" or "1, 2,  3")
        $inputWards = preg_replace('/ward\s*/i', '', $wards);
        $inputWardParts = array_map('trim', explode(',', $inputWards));
        $inputWardNumbers = array_filter(array_map('intval', $inputWardParts), function($val) {
            return $val > 0; // Filter out 0 values (from empty strings or invalid input)
        });

        if (empty($inputWardNumbers)) {
            return true; // No valid ward numbers found, skip validation
        }

        // Parse wards from database (format: "1,2,3" or "1, 2, 3")
        $dbWardParts = array_map('trim', explode(',', $streetlight->ward));
        $dbWardNumbers = array_filter(array_map('intval', $dbWardParts), function($val) {
            return $val > 0; // Filter out 0 values
        });

        if (empty($dbWardNumbers)) {
            return false; // No wards in DB but wards specified in import
        }

        // Check if all input wards exist in database wards
        foreach ($inputWardNumbers as $ward) {
            if (!in_array($ward, $dbWardNumbers)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add error to errors array
     */
    private function addError(int $row, string $panchayat, string $engineerName, string $vendorName, string $wards, string $reason): void
    {
        $this->errors[] = [
            'row' => $row,
            'panchayat' => $panchayat,
            'engineer_name' => $engineerName,
            'vendor_name' => $vendorName,
            'wards' => $wards,
            'reason' => $reason
        ];
    }

    /**
     * Get errors array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get imported count
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    /**
     * Get multiple matches array (for admin confirmation dialogs)
     */
    public function getMultipleMatches(): array
    {
        return $this->multipleMatches;
    }
}

