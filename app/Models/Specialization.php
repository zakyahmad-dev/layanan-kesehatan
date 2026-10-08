<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Specialization extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];
    public function doctors(): HasMany { return $this->hasMany(Doctor::class); }
    public function polyclinics(): HasMany { return $this->hasMany(Polyclinic::class); }
}
