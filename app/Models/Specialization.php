<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

    public function polyclinics()
    {
        return $this->hasMany(Polyclinic::class);
    }
}