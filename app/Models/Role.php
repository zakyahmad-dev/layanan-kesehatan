<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Role extends Model
{
    protected $guarded = [];
    public function users() { return $this->belongsToMany(User::class, 'role_users')->withTimestamps(); }
    public function permissions() { return $this->belongsToMany(Permission::class, 'permission_roles')->withTimestamps(); }
}
