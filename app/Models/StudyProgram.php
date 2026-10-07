<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'code',
        'nim_code',
        'name',
    ];

    /**
     * Faculty where this study program belongs.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    /**
     * User profiles enrolled or teaching in this study program.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(UserProfile::class, 'study_program_id');
    }
}
