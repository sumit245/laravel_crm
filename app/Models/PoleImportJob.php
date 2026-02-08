<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PoleImportJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'file_path',
        'total_rows',
        'processed_rows',
        'success_count',
        'error_count',
        'status',
        'error_file_path',
        'user_id',
        'project_id',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'success_count' => 'integer',
        'error_count' => 'integer',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($job) {
            if (empty($job->job_id)) {
                $job->job_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user who initiated the import
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_rows == 0) {
            return 0;
        }
        return round(($this->processed_rows / $this->total_rows) * 100, 2);
    }

    /**
     * Check if job is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if job is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if job is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Mark job as processing
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark job as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark job as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_file_path' => $errorMessage, // Can store error message or file path
            'completed_at' => now(),
        ]);
    }

    /**
     * Add processed row count
     */
    public function addProcessedRows(int $count, int $successCount = 0, int $errorCount = 0): void
    {
        $this->increment('processed_rows', $count);
        if ($successCount > 0) {
            $this->increment('success_count', $successCount);
        }
        if ($errorCount > 0) {
            $this->increment('error_count', $errorCount);
        }
    }
}

