<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class DoctorSchedule extends Model
{
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class); }
}
