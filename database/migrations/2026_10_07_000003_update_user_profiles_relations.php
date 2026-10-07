<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->foreignId('work_unit_id')->nullable()->after('nomor_induk')->constrained('work_units')->nullOnDelete();
            $table->foreignId('study_program_id')->nullable()->after('work_unit_id')->constrained('study_programs')->nullOnDelete();

            $table->dropColumn(['unit_kerja', 'fakultas', 'program_studi', 'status_akademik']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('unit_kerja')->nullable();
            $table->string('fakultas')->nullable();
            $table->string('program_studi')->nullable();
            $table->string('status_akademik')->nullable();

            $table->dropForeign(['work_unit_id']);
            $table->dropForeign(['study_program_id']);
            $table->dropColumn(['work_unit_id', 'study_program_id']);
        });
    }
};
