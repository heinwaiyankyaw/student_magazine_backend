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
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('file_path');
            $table->string('image_path');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('faculty_id')->constrained('faculties')->onDelete('cascade');
            $table->enum('status', ['pending', 'reviewed', 'selected', 'rejected'])->default('pending');
            $table->boolean('active_flag')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
