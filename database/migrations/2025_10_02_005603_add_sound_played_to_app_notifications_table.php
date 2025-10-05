<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('app_notifications', 'sound_played')) {
                $table->boolean('sound_played')->default(false)->after('read_at');
            }
        });
    }

    public function down()
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            $table->dropColumn('sound_played');
        });
    }
};