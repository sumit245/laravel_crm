<?php


namespace App\Imports;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StaffImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;
    public int $skipped = 0;
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                // normalize values (heading row keys are already lowercased by WithHeadingRow)
                $first = trim((string) ($row['first_name'] ?? $row['firstname'] ?? $row['first'] ?? ''));
                $last = trim((string) ($row['last_name'] ?? $row['lastname'] ?? $row['last'] ?? ''));
                $email = strtolower(trim((string) ($row['email'] ?? '')));
                $password = trim((string) ($row['password'] ?? ''));
                $phone = trim((string) ($row['contactno'] ?? $row['contact_number'] ?? $row['mobile'] ?? $row['mobile_number'] ?? $row['phone'] ?? ''));
                $roleRaw = trim((string) ($row['role'] ?? $row['role_id'] ?? ''));
                $employeeId = trim((string) ($row['employee_code'] ?? $row['emp_code_id'] ?? ''));
                $address = trim((string) ($row['address'] ?? ''));
                $projectName = trim((string) ($row['project'] ?? ''));
                $projectId = trim((string) ($row['project_id'] ?? ''));
                $category = trim((string) ($row['category'] ?? ''));

                // minimal required: first name + email
                if (empty($first) || empty($email)) {
                    $this->skipped++;
                    $this->errors[] = "Row " . ($index + 2) . " skipped: missing first_name or email.";
                    continue;
                }

                // skip existing by email
                if (User::where('email', $email)->exists()) {
                    $this->skipped++;
                    continue;
                }

                // resolve role id
                $roleId = 10; // default Coordinator (adjust as needed)
                if (is_numeric($roleRaw)) {
                    $roleId = (int) $roleRaw;
                } else {
                    $map = [
                        'admin' => 0,
                        'site engineer' => 1,
                        'project manager' => 2,
                        'vendor' => 3,
                        'store incharge' => 4,
                        'coordinator' => 5,
                    ];
                    $lower = strtolower($roleRaw);
                    $roleId = $map[$lower] ?? 5;
                }

                // attempt to resolve project id by name if needed
                if (empty($projectId) && $projectName) {
                    $proj = Project::where('project_name', $projectName)->first();
                    $projectId = $proj ? $proj->id : null;
                } elseif (!is_numeric($projectId)) {
                    $projectId = null;
                } else {
                    $projectId = (int) $projectId;
                }

                // create unique username
                $base = Str::lower(preg_replace('/\s+/', '', $first));
                $username = $base . mt_rand(1000, 9999);
                while (User::where('username', $username)->exists()) {
                    $username = $base . mt_rand(1000, 9999);
                }

                $plainPassword = $password ?: Str::random(8);

                try {
                    DB::beginTransaction();

                    $user = User::create([
                        'name' => $first . ($last ? ' ' . $last : ''),
                        'email' => $email,
                        'username' => $username,
                        'firstName' => $first,
                        'lastName' => $last ?: null,
                        'password' => bcrypt($plainPassword),
                        'contactNo' => $phone ?: null,
                        'address' => $address ?: null,
                        'role' => $roleId,
                        'category' => $category ?? null,

                    ]);

                    // attach to project pivot (project_user) if project id present
                    if (!empty($projectId) && is_numeric($projectId)) {
                        DB::table('project_user')->insert([
                            'user_id' => $user->id,
                            'project_id' => (int) $projectId,
                            'role' => $roleId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::commit();
                    $this->created++;
                } catch (\Throwable $e) {
                    // ensure rollback if transaction started
                    try {
                        DB::rollBack();
                    } catch (\Throwable $inner) {
                        // ignore
                    }
                    $this->skipped++;
                    $this->errors[] = "Row " . ($index + 2) . " error: " . $e->getMessage();
                    Log::error("StaffImport row " . ($index + 2) . " DB error: " . $e->getMessage(), ['row' => $row]);
                    continue;
                }
            } catch (\Throwable $e) {
                // catch runtime / unexpected errors per-row and continue
                $this->skipped++;
                $this->errors[] = "Row " . ($index + 2) . " runtime error: " . $e->getMessage();
                Log::error("StaffImport row " . ($index + 2) . " runtime error: " . $e->getMessage(), ['row' => $row]);
                continue;
            }
        }
    }

    public function getSummary(): array
    {
        return [
            'created' => $this->created,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
            'message' => "Import complete. Created: {$this->created}. Skipped: {$this->skipped}.",
        ];
    }
}
