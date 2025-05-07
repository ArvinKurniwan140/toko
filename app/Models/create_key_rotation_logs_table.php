<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class create_key_rotation_logs_table extends Model
{
    use HasFactory;

    protected $fillable = [
        'rotation_date',
        'performed_by',
        'status',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rotation_date' => 'datetime',
    ];
}
