<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AppointmentReminder extends Model
{
    protected $guarded = [];
    protected $casts = ['scheduled_for' => 'datetime', 'sent_at' => 'datetime'];
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
}
