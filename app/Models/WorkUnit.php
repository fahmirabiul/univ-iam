<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * User profiles assigned to this work unit.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(UserProfile::class, 'work_unit_id');
    }
}
