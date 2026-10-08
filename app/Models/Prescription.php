<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Prescription extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['ready_at' => 'datetime', 'completed_at' => 'datetime'];
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class); }
    public function medicalRecord(): BelongsTo { return $this->belongsTo(MedicalRecord::class); }
    public function items(): HasMany { return $this->hasMany(PrescriptionItem::class); }
    public function processor(): BelongsTo { return $this->belongsTo(User::class, 'processed_by'); }
}
