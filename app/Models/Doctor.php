<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Doctor extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['education' => 'array', 'is_active' => 'boolean'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function specialization(): BelongsTo { return $this->belongsTo(Specialization::class); }
    public function polyclinic(): BelongsTo { return $this->belongsTo(Polyclinic::class); }
    public function schedules(): HasMany { return $this->hasMany(DoctorSchedule::class); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class); }
}
