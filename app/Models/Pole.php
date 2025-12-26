<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pole extends Model
{
    use HasFactory;
    protected $table = 'streelight_poles';
    protected $fillable = [
        'task_id',
        'vendor_id',
        'isSurveyDone',
        'isNetworkAvailable',
        'isInstallationDone',
        'luminary_qr',
        'battery_qr',
        'panel_qr',
        'complete_pole_number',
        'beneficiary',
        'remarks',
        'ward_name',
        'beneficiary_contact',
        'sim_number',
        'submission_image',
        'survey_image',
        'lat',
        'lng',
        'file'
    ];
    protected $casts = [
        'survey_image' => 'array',
    ];

    /**
     * Note: Pole does not have direct streetlight_id foreign key.
     * To access streetlight: $pole->task->streetlight
     * Relationship path: Pole → StreetlightTask (via task_id) → Streetlight (via site_id)
     */
    
    /**
     * Get the streetlight task this pole belongs to
     * Relationship: Pole belongs to StreetlightTask
     * Foreign Key: streelight_poles.task_id → streetlight_tasks.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task()
    {
        return $this->belongsTo(StreetlightTask::class, 'task_id');
    }

    /**
     * Get the vendor assigned to this pole
     * Relationship: Pole belongs to User (Vendor)
     * Foreign Key: streelight_poles.vendor_id → users.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get all inventory dispatches consumed by this pole
     * Relationship: Pole has many InventoryDispatches
     * Foreign Key: inventory_dispatch.streetlight_pole_id → streelight_poles.id
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventoryDispatches()
    {
        return $this->hasMany(InventoryDispatch::class, 'streetlight_pole_id');
    }
}
