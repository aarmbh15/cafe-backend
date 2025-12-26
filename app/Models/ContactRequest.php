<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'service',
        'preferred_date',
        'message',
        'read',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'read' => 'boolean',
    ];
}