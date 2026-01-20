<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TargetDeletionJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'task_ids',
        'processed_task_ids',
        'total_tasks',
        'processed_tasks',
        'total_poles',
        'processed_poles',
        'status',
        'error_message',
        'user_id',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'task_ids' => 'array',
        'processed_task_ids' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
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
     * Get the user who initiated the deletion
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
        if ($this->total_tasks == 0) {
            return 0;
        }
        return round(($this->processed_tasks / $this->total_tasks) * 100, 2);
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
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    /**
     * Add processed task ID
     */
    public function addProcessedTaskId(int $taskId): void
    {
        $processed = $this->processed_task_ids ?? [];
        if (!in_array($taskId, $processed)) {
            $processed[] = $taskId;
            $this->update([
                'processed_task_ids' => $processed,
                'processed_tasks' => count($processed),
            ]);
        }
    }
}
