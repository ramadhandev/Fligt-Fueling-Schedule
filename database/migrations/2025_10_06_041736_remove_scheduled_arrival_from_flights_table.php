<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dropColumn('scheduled_arrival');
        });
    }

    public function down()
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dateTime('scheduled_arrival')->nullable();
        });
    }
};