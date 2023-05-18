<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasUlids;

    protected $casts = [
        'by_user_deleted_at' => 'datetime',
    ];

    protected $guarded = [];
}
