<?php


namespace App\Imports;

use App\Models\Project;
use App\Models\User;
use App\Models\UserCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class StaffImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowArray = null; // Initialize outside try block for catch blocks
            try {
                // Convert row to array if it's a Collection
                $rowArray = $row instanceof \Illuminate\Support\Collection ? $row->toArray() : (array) $row;

                // Debug: Log first row to see actual keys (only once)
                if ($index === 0) {
                    Log::info('First row keys', ['keys' => array_keys($rowArray)]);
                }

                // normalize values - WithHeadingRow converts spaces to underscores and lowercases
                // "Contact Number" becomes "contact_number", "Reporting Manager " becomes "reporting_manager" or "reporting_manager " (with trailing space)
                $first = trim((string) ($rowArray['firstname'] ?? $rowArray['first_name'] ?? $rowArray['first'] ?? ''));
                $last = trim((string) ($rowArray['lastname'] ?? $rowArray['last_name'] ?? $rowArray['last'] ?? ''));
                $email = strtolower(trim((string) ($rowArray['email'] ?? '')));
                $password = trim((string) ($rowArray['password'] ?? ''));
                // Try multiple variations for contact number (WithHeadingRow converts "Contact Number" to "contact_number")
                $phone = trim((string) ($rowArray['contact_number'] ?? $rowArray['contactnumber'] ?? $rowArray['contactno'] ?? $rowArray['mobile'] ?? $rowArray['mobile_number'] ?? $rowArray['phone'] ?? ''));
                $roleRaw = trim((string) ($rowArray['role'] ?? $rowArray['role_id'] ?? ''));
                // Employee code variations (WithHeadingRow converts "Employee Code" to "employee_code")
                $employeeId = trim((string) ($rowArray['employee_code'] ?? $rowArray['employeecode'] ?? $rowArray['emp_code_id'] ?? ''));
                $address = trim((string) ($rowArray['address'] ?? ''));
                $projectName = trim((string) ($rowArray['project'] ?? ''));
                $projectId = trim((string) ($rowArray['project_id'] ?? ''));
                $categoryCode = trim((string) ($rowArray['category'] ?? ''));
                $department = trim((string) ($rowArray['department'] ?? ''));
                // Reporting Manager - try variations (WithHeadingRow converts "Reporting Manager " to "reporting_manager" or "reporting_manager ")
                $reportingManager = trim((string) ($rowArray['reporting_manager'] ?? $rowArray['reporting_manager '] ?? $rowArray['reportingmanager'] ?? ''));
                // Vertical Head - same logic (WithHeadingRow converts "Vertical Head" to "vertical_head")
                $verticalHead = trim((string) ($rowArray['vertical_head'] ?? $rowArray['verticalhead'] ?? ''));

                // minimal required: first name + email
                if (empty($first) || empty($email)) {
                    $this->skipped++;
                    $this->errors[] = "Row " . ($index + 2) . " skipped: missing firstname or email.";
                    continue;
                }

                // Handle password - Excel formulas like "=B2&\"@123\"" need to be processed
                // For now, generate password based on firstname + lastname
                if (empty($password) || strpos($password, '=') === 0) {
                    // Generate password: firstname@123 or firstname.lastname@123
                    $plainPassword = Str::lower($first) . (!empty($last) ? '.' . Str::lower($last) : '') . '@123';
                } else {
                    $plainPassword = $password;
                }

                // Check if user exists
                $existingUser = User::where('email', $email)->first();
                $isUpdate = $existingUser !== null;

                // Resolve role id
                $roleId = $this->mapRoleToId($roleRaw);

                // Resolve category by category_code
                $categoryId = null;
                if (!empty($categoryCode)) {
                    try {
                        $userCategory = UserCategory::firstOrCreate(
                            ['category_code' => $categoryCode],
                            ['name' => $categoryCode, 'description' => 'Imported from Excel']
                        );
                        $categoryId = $userCategory->id;
                    } catch (\Exception $e) {
                        Log::warning("Category creation failed for: {$categoryCode}", ['error' => $e->getMessage()]);
                        $categoryId = null;
                    }
                }

                // Resolve reporting manager by name
                $managerId = $this->findUserByName($reportingManager);

                // Resolve vertical head by name
                $verticalHeadId = $this->findUserByName($verticalHead);

                // Attempt to resolve project id by name if needed
                if (empty($projectId) && $projectName) {
                    $proj = Project::where('project_name', $projectName)->first();
                    $projectId = $proj ? $proj->id : null;
                } elseif (!is_numeric($projectId)) {
                    $projectId = null;
                } else {
                    $projectId = (int) $projectId;
                }

                // Create unique username if new user
                if (!$isUpdate) {
                    $base = Str::lower(preg_replace('/\s+/', '', $first));
                    $username = $base . mt_rand(1000, 9999);
                    while (User::where('username', $username)->exists()) {
                        $username = $base . mt_rand(1000, 9999);
                    }
                } else {
                    $username = $existingUser->username;
                }

                try {
                    DB::beginTransaction();

                    $userData = [
                        'name' => $first . ($last ? ' ' . $last : ''),
                        'email' => $email,
                        'username' => $username,
                        'firstName' => $first,
                        'lastName' => $last ?: null,
                        'contactNo' => $phone ?: null,
                        'address' => $address ?: null,
                        'role' => $roleId,
                        'department' => $department ?: null,
                        'category' => $categoryId,
                        'manager_id' => $managerId,
                        'vertical_head_id' => $verticalHeadId,
                    ];

                    // Only update password if user is new or password is explicitly provided
                    if (!$isUpdate || (!empty($password) && strpos($password, '=') !== 0)) {
                        $userData['password'] = bcrypt($plainPassword);
                    }

                    if ($isUpdate) {
                        $existingUser->update($userData);
                        $user = $existingUser;
                        $this->updated++;
                    } else {
                        $user = User::create($userData);
                        $this->created++;
                    }

                    // Handle project assignment via pivot table
                    if (!empty($projectId) && is_numeric($projectId)) {
                        // Remove existing project assignments for this user
                        DB::table('project_user')->where('user_id', $user->id)->delete();

                        // Insert new project assignment
                        DB::table('project_user')->insert([
                            'user_id' => $user->id,
                            'project_id' => (int) $projectId,
                            'role' => (string) $roleId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::commit();
                } catch (\Throwable $e) {
                    // ensure rollback if transaction started
                    try {
                        DB::rollBack();
                    } catch (\Throwable $inner) {
                        // ignore
                    }
                    $this->skipped++;
                    $errorMsg = "Row " . ($index + 2) . " error: " . $e->getMessage();
                    $this->errors[] = $errorMsg;
                    Log::error("StaffImport row " . ($index + 2) . " DB error: " . $e->getMessage(), ['row' => $rowArray ?? []]);
                    continue;
                }
            } catch (\Throwable $e) {
                // catch runtime / unexpected errors per-row and continue
                $this->skipped++;
                $errorMsg = "Row " . ($index + 2) . " runtime error: " . $e->getMessage();
                $this->errors[] = $errorMsg;
                Log::error("StaffImport row " . ($index + 2) . " runtime error: " . $e->getMessage(), ['row' => $rowArray ?? []]);
                continue;
            }
        }
    }

    /**
     * Map role name to role ID
     */
    private function mapRoleToId(string $roleRaw): int
    {
        if (empty($roleRaw)) {
            return 5; // Default to Coordinator
        }

        if (is_numeric($roleRaw)) {
            return (int) $roleRaw;
        }

        $roleMap = [
            // Admin roles
            'admin' => 0,
            'administrator' => 0,

            // Engineer roles
            'site engineer' => 1,
            'junior engineer' => 1,
            'engineer' => 1,
            'senior engineer' => 1,

            // Manager roles
            'project manager' => 2,
            'assistant vice president' => 2,
            'avp' => 2,
            'vice president' => 2,
            'vp' => 2,

            // Vendor
            'vendor' => 3,

            // Store roles
            'store incharge' => 4,
            'store manager' => 4,
            'store incharge' => 4,

            // Executive/Coordinator roles (default)
            'executive' => 5,
            'senior executive' => 5,
            'coordinator' => 5,
        ];

        $lower = strtolower(trim($roleRaw));
        return $roleMap[$lower] ?? 5; // Default to Coordinator
    }

    /**
     * Find user by name (for Reporting Manager and Vertical Head)
     */
    private function findUserByName(?string $name): ?int
    {
        if (empty($name)) {
            return null;
        }

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

        $firstName = $nameParts[0];
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : null;

        // Try exact match first
        $query = User::where('firstName', 'like', $firstName . '%');
        if ($lastName) {
            $query->where(function ($q) use ($lastName) {
                $q->where('lastName', 'like', $lastName . '%')
                    ->orWhere('lastName', 'like', '%' . $lastName . '%');
            });
        }

        $user = $query->first();

        // If not found, try fuzzy matching with just first name
        if (!$user && $firstName) {
            $user = User::where('firstName', 'like', $firstName . '%')
                ->orWhere('name', 'like', '%' . $firstName . '%')
                ->first();
        }

        return $user ? $user->id : null;
    }

    public function getSummary(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
            'message' => "Import complete. Created: {$this->created}, Updated: {$this->updated}, Skipped: {$this->skipped}.",
        ];
    }
}
