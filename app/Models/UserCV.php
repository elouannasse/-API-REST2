<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCV extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'path',
        'original_name',
        'mime_type',
        'size',
        'extracted_data',
    ];

    protected $casts = [
        'extracted_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}