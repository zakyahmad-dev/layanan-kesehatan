<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Appointment extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = [
        'scheduled_at' => 'datetime', 'reminder_sent_at' => 'datetime', 'cancelled_at' => 'datetime',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class); }
    public function polyclinic(): BelongsTo { return $this->belongsTo(Polyclinic::class); }
    public function queue(): ?\Illuminate\Database\Eloquent\Relations\HasOne { return $this->hasOne(Queue::class); }
    public function reminders(): HasMany { return $this->hasMany(AppointmentReminder::class); }
}
