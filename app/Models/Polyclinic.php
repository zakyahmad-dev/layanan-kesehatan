<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Polyclinic extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];
    public function specialization(): BelongsTo { return $this->belongsTo(Specialization::class); }
    public function doctors(): HasMany { return $this->hasMany(Doctor::class); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class); }
    public function queues(): HasMany { return $this->hasMany(Queue::class); }
    public function services(): HasMany { return $this->hasMany(Service::class); }
}
