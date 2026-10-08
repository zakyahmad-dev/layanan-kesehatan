<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Queue extends Model
{
    protected $table = 'queues';
    protected $guarded = [];
    protected $casts = ['called_at' => 'datetime', 'serving_at' => 'datetime', 'completed_at' => 'datetime'];
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
    public function polyclinic(): BelongsTo { return $this->belongsTo(Polyclinic::class); }
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class); }
}
