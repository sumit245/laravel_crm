<?php

namespace App\Imports;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class VendorImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
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
                    Log::info('VendorImport first row keys', ['keys' => array_keys($rowArray)]);
                }

                // Normalize values - WithHeadingRow converts spaces to underscores and lowercases
                $first = trim((string) ($rowArray['firstname'] ?? $rowArray['first_name'] ?? $rowArray['first'] ?? ''));
                $last = trim((string) ($rowArray['lastname'] ?? $rowArray['last_name'] ?? $rowArray['last'] ?? ''));
                $name = trim((string) ($rowArray['name'] ?? ($first . ' ' . $last)));
                $email = strtolower(trim((string) ($rowArray['email'] ?? '')));
                $password = trim((string) ($rowArray['password'] ?? ''));
                $phone = trim((string) ($rowArray['contact_number'] ?? $rowArray['contactnumber'] ?? $rowArray['contactno'] ?? $rowArray['mobile'] ?? $rowArray['mobile_number'] ?? $rowArray['phone'] ?? ''));
                $address = trim((string) ($rowArray['address'] ?? ''));
                
                // Project assignment
                $projectName = trim((string) ($rowArray['project'] ?? $rowArray['project_name'] ?? ''));
                $projectId = trim((string) ($rowArray['project_id'] ?? ''));
                
                // Manager assignment
                $managerName = trim((string) ($rowArray['manager'] ?? $rowArray['manager_name'] ?? $rowArray['reporting_manager'] ?? ''));
                $managerId = trim((string) ($rowArray['manager_id'] ?? ''));
                
                // Bank details
                $accountName = trim((string) ($rowArray['account_name'] ?? $rowArray['accountname'] ?? ''));
                $accountNumber = trim((string) ($rowArray['account_number'] ?? $rowArray['accountnumber'] ?? ''));
                $ifsc = trim((string) ($rowArray['ifsc'] ?? $rowArray['ifsc_code'] ?? ''));
                $bankName = trim((string) ($rowArray['bank_name'] ?? $rowArray['bankname'] ?? ''));
                $branch = trim((string) ($rowArray['branch'] ?? $rowArray['branch_name'] ?? ''));
                
                // Tax details
                $pan = trim((string) ($rowArray['pan'] ?? $rowArray['pan_number'] ?? ''));
                $gstNumber = trim((string) ($rowArray['gst_number'] ?? $rowArray['gstnumber'] ?? $rowArray['gst'] ?? ''));
                $aadharNumber = trim((string) ($rowArray['aadhar_number'] ?? $rowArray['aadharnumber'] ?? $rowArray['aadhar'] ?? ''));

                // Minimal required: first name + email
                if (empty($first) || empty($email)) {
                    $this->skipped++;
                    $this->errors[] = "Row " . ($index + 2) . " skipped: missing firstname or email.";
                    continue;
                }

                // Handle password - Excel formulas like "=B2&\"@123\"" need to be processed
                // For now, generate password based on firstname + lastname
                if (empty($password) || strpos($password, '=') === 0) {
                    // Generate password: firstname.lastname@123
                    $plainPassword = Str::lower($first) . (!empty($last) ? '.' . Str::lower($last) : '') . '@123';
                } else {
                    $plainPassword = $password;
                }

                // Check if user exists
                $existingUser = User::where('email', $email)->first();
                $isUpdate = $existingUser !== null;

                // Resolve manager by name
                $resolvedManagerId = $this->findUserByName($managerName);
                if (!empty($managerId) && is_numeric($managerId)) {
                    $resolvedManagerId = (int) $managerId;
                }

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
                        'name' => $name ?: ($first . ($last ? ' ' . $last : '')),
                        'email' => $email,
                        'username' => $username,
                        'firstName' => $first,
                        'lastName' => $last ?: null,
                        'contactNo' => $phone ?: null,
                        'address' => $address ?: null,
                        'role' => UserRole::VENDOR->value,
                        'manager_id' => $resolvedManagerId,
                        'project_id' => $projectId,
                        'accountName' => $accountName ?: null,
                        'accountNumber' => $accountNumber ?: null,
                        'ifsc' => $ifsc ?: null,
                        'bankName' => $bankName ?: null,
                        'branch' => $branch ?: null,
                        'pan' => $pan ?: null,
                        'gstNumber' => $gstNumber ?: null,
                        'aadharNumber' => $aadharNumber ?: null,
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
                        // Sync project assignment (add if not exists)
                        $user->assignToProject($projectId);
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
                    Log::error("VendorImport row " . ($index + 2) . " DB error: " . $e->getMessage(), ['row' => $rowArray ?? []]);
                    continue;
                }
            } catch (\Throwable $e) {
                // catch runtime / unexpected errors per-row and continue
                $this->skipped++;
                $errorMsg = "Row " . ($index + 2) . " runtime error: " . $e->getMessage();
                $this->errors[] = $errorMsg;
                Log::error("VendorImport row " . ($index + 2) . " runtime error: " . $e->getMessage(), ['row' => $rowArray ?? []]);
                continue;
            }
        }
    }

    /**
     * Find user by name (for Manager)
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

