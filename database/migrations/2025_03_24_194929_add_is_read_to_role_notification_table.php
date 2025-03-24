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
        Schema::table('role_notification', function (Blueprint $table) {
            $table->boolean('is_read')->default(false)->after('notification_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('role_notification', function (Blueprint $table) {
            $table->dropColumn('is_read');
        });
    }
};
