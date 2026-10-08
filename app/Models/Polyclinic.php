<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Polyclinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'specialization_id',
        'name',
        'slug',
        'location_note',
        'queue_prefix',
    ];

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}