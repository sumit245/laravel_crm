<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Streetlight extends Model
{
    use HasFactory;
    protected $fillable = [
        'task_id',
        'state',
        'district',
        'block',
        'panchayat',
        'ward',
        'pole',
        'complete_pole_number',
        'uname',
        'SID',
        'district_id',
        'block_id',
        'panchayat_id',
        'ward_id',
        'pole_id',
        'luminary_qr',
        'battery_qr',
        'panel_qr',
        'file',
        'lat',
        'lng',
        'beneficiary',
        'remark'
    ];
}
