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
            $table->string('article_path');
            $table->json('image_paths')->nullable;
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('faculty_id')->constrained('faculties')->onDelete('cascade');
            $table->enum('status', ['pending', 'reviewed', 'selected', 'rejected'])->default('pending');
            $table->boolean('active_flag')->default(true);
            $table->timestamps();
            $table->unsignedBigInteger('createby');
            $table->unsignedBigInteger('updateby')->nullable();
            $table->foreign('createby')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updateby')->references('id')->on('users')->onDelete('cascade');
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
