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
        Schema::table('users', function (Blueprint $table) {
            $table->string('student_code', 50)->nullable()->unique();
            $table->string('teacher_code', 50)->nullable()->unique();
            $table->string('department', 120)->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['student_code']);
            $table->dropUnique(['teacher_code']);
            $table->dropIndex(['department']);
            $table->dropColumn(['student_code', 'teacher_code', 'department']);
        });
    }
};
