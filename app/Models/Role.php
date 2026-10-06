<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    public const TYPE_CIVITAS = 'civitas';
    public const TYPE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'description',
    ];

    /**
     * Scope a query to only include civitas/identity roles.
     */
    public function scopeCivitas(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_CIVITAS);
    }

    /**
     * Scope a query to only include administrative roles.
     */
    public function scopeAdmin(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_ADMIN);
    }

    /**
     * The users that belong to the role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id')
            ->withTimestamps();
    }
}
