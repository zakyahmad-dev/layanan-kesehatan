<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class PrescriptionItem extends Model
{
    protected $guarded = [];
    public function prescription(): BelongsTo { return $this->belongsTo(Prescription::class); }
    public function medicine(): BelongsTo { return $this->belongsTo(Medicine::class); }
}
