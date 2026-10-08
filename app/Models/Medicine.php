<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Medicine extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean', 'requires_prescription' => 'boolean'];
    public function stockMovements(): HasMany { return $this->hasMany(MedicineStock::class); }
    public function getIsLowStockAttribute(): bool { return $this->current_stock <= $this->min_stock; }
}
