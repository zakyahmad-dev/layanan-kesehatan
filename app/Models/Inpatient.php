<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Inpatient extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['admitted_at' => 'datetime', 'checked_out_at' => 'datetime'];
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class); }
    public function bed(): BelongsTo { return $this->belongsTo(Bed::class); }
    public function transfers(): HasMany { return $this->hasMany(InpatientTransfer::class); }
}
