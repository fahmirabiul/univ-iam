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
     * Get the user's primary civitas/identity role (dosen, mahasiswa, karyawan).
     */
    public function getCivitasRole(): ?Role
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->firstWhere('type', Role::TYPE_CIVITAS)
                ?? $this->roles->first(fn (Role $r): bool => in_array($r->name, ['dosen', 'mahasiswa', 'karyawan'], true))
                ?? $this->roles->first();
        }

        return $this->roles()->where('type', Role::TYPE_CIVITAS)->first()
            ?? $this->roles()->whereIn('name', ['dosen', 'mahasiswa', 'karyawan'])->first()
            ?? $this->roles()->first();
    }

    /**
     * Get the administrative roles assigned to the user (e.g. admin_sdm, admin_lppm, super_admin).
     *
     * @return \Illuminate\Support\Collection<int, Role>
     */
    public function getAdminRoles(): \Illuminate\Support\Collection
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles
                ->filter(fn (Role $r): bool => $r->type === Role::TYPE_ADMIN || in_array($r->name, ['super_admin', 'admin_sdm', 'admin_lppm'], true))
                ->values();
        }

        return $this->roles()
            ->where(function ($q): void {
                $q->where('type', Role::TYPE_ADMIN)
                    ->orWhereIn('name', ['super_admin', 'admin_sdm', 'admin_lppm']);
            })
            ->get();
    }
}
