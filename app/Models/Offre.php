<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offre extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'company',
        'location',
        'contract_type',
        'category',
        'status',
    ];
    public function candidatures()
    {
        return $this->hasMany(Candidature::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'candidatures');
    }
}


