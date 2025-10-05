<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Hapus kolom is_whatsapp_opt_in dari users table JIKA ADA
        if (Schema::hasColumn('users', 'is_whatsapp_opt_in')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_whatsapp_opt_in');
            });
        }

        // Hapus table notifications JIKA ADA
        if (Schema::hasTable('notifications')) {
            Schema::dropIfExists('notifications');
        }
    }

    public function down()
    {
        // Recreate notifications table untuk rollback
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users');
                $table->foreignId('fuel_schedule_id')->constrained('fuel_schedules');
                $table->string('type');
                $table->string('channel');
                $table->text('message');
                $table->dateTime('sent_at')->nullable();
                $table->boolean('success')->default(false);
                $table->timestamps();
            });
        }

        // Add back whatsapp column untuk rollback
        if (!Schema::hasColumn('users', 'is_whatsapp_opt_in')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_whatsapp_opt_in')->default(false);
            });
        }
    }
};