<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AiConsultation extends Model
{
    protected $guarded = [];
    protected $casts = ['emergency_flag' => 'boolean', 'started_at' => 'datetime', 'ended_at' => 'datetime'];
    public function messages(): HasMany { return $this->hasMany(AiMessage::class, 'ai_consultation_id'); }
    public function recommendations(): HasMany { return $this->hasMany(AiRecommendation::class, 'ai_consultation_id'); }
}
