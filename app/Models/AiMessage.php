<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AiMessage extends Model
{
    protected $guarded = [];
    protected $casts = ['extracted_symptoms' => 'array', 'safety_flags' => 'array'];
    public function consultation(): BelongsTo { return $this->belongsTo(AiConsultation::class, 'ai_consultation_id'); }
}
