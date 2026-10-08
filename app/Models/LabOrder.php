<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class LabOrder extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['completed_at' => 'datetime', 'released_to_patient_at' => 'datetime'];
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function orderer(): BelongsTo { return $this->belongsTo(Doctor::class, 'ordered_by'); }
    public function results(): HasMany { return $this->hasMany(LabResult::class); }
}
