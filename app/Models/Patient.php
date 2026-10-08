<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Patient extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['consent_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class); }
    public function medicalRecords(): HasMany { return $this->hasMany(MedicalRecord::class); }
    public function vitalSigns(): HasMany { return $this->hasMany(VitalSign::class); }
    public function prescriptions(): HasMany { return $this->hasMany(Prescription::class); }
    public function labOrders(): HasMany { return $this->hasMany(LabOrder::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }

    public function getAgeAttribute(): int
    {
        return \Carbon\Carbon::parse($this->birth_date)->age;
    }
}
