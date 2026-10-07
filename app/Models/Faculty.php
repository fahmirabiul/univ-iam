<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * Study programs belonging to this faculty.
     */
    public function studyPrograms(): HasMany
    {
        return $this->hasMany(StudyProgram::class, 'faculty_id');
    }
}
