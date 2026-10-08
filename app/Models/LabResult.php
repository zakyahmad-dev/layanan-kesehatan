<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class LabResult extends Model
{
    protected $guarded = [];
    protected $casts = ['verified_at' => 'datetime'];
    public function testType(): BelongsTo { return $this->belongsTo(LabTestType::class, 'lab_test_type_id'); }
    public function labOrder(): BelongsTo { return $this->belongsTo(LabOrder::class); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
}
