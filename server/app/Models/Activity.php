<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'title',
        'description',
        'slug',
        'points_label',
        'mode',
        'is_active',
        'created_by_id',
    ];
}
