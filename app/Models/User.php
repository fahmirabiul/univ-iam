<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'is_active',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get the user's demographic profile.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->hasAnyRole($role);
    }

    /**
     * Check if the user has any of the given roles.
     *
     * @param  array<int, string>|string  ...$roles
     */
    public function hasAnyRole(array|string ...$roles): bool
    {
        $flattenedRoles = collect($roles)->flatten()->all();

        if ($this->relationLoaded('roles')) {
            return $this->roles->pluck('name')->intersect($flattenedRoles)->isNotEmpty();
        }

        return $this->roles()->whereIn('name', $flattenedRoles)->exists();
    }

    /**
     * Check if user is the Super Administrator.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if user is an administrator of their assigned work unit.
     */
    public function isUnitAdmin(): bool
    {
        return (bool) $this->is_admin && $this->profile?->work_unit_id !== null;
    }

    /**
     * Get the user's primary civitas/identity role (dosen, mahasiswa, karyawan) or super_admin.
     */
    public function getCivitasRole(): ?Role
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->first(fn (Role $r): bool => in_array($r->name, ['dosen', 'karyawan', 'mahasiswa'], true))
                ?? $this->roles->first();
        }

        return $this->roles()->whereIn('name', ['dosen', 'karyawan', 'mahasiswa'])->first()
            ?? $this->roles()->first();
    }

    /**
     * Get the user's assigned work unit if available.
     */
    public function getAssignedWorkUnit(): ?WorkUnit
    {
        return $this->profile?->workUnit;
    }
}
