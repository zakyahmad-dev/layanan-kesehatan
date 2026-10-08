<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class MedicalRecord extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['finalized_at' => 'datetime'];
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class); }
    public function details(): HasMany { return $this->hasMany(MedicalRecordDetail::class); }
    public function diagnoses(): HasMany { return $this->details()->where('type', 'diagnosis'); }
    public function treatments(): HasMany { return $this->details()->where('type', 'treatment'); }
    public function prescription(): ?\Illuminate\Database\Eloquent\Relations\HasOne { return $this->hasOne(Prescription::class); }
    public function labOrder(): ?\Illuminate\Database\Eloquent\Relations\HasOne { return $this->hasOne(LabOrder::class); }
    public function vitalSigns(): HasMany { return $this->hasMany(VitalSign::class); }
}
