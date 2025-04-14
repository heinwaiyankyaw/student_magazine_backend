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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year')->unique();
            $table->date('closure_date')->nullable();
            $table->date('final_closure_date')->nullable();
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
        Schema::dropIfExists('system_settings');
    }
};
