<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AiRecommendation extends Model
{
    protected $guarded = [];
    protected $casts = ['disclaimer_shown_at' => 'datetime'];
    public function doctors(): HasMany { return $this->hasMany(AiRecommendationDoctor::class, 'ai_recommendation_id'); }
}
