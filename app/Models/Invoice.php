<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Invoice extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['issued_at' => 'datetime', 'due_at' => 'datetime', 'paid_at' => 'datetime'];
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class); }
    public function payment(): ?\Illuminate\Database\Eloquent\Relations\HasOne { return $this->hasOne(Payment::class); }
}
