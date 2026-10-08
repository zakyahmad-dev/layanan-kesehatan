<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Bed extends Model
{
    protected $guarded = [];
    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
    public function activeStay(): ?\Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Inpatient::class)->where('status', 'admitted');
    }
}
