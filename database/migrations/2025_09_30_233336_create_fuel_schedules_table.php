<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fuel_schedules', function (Blueprint $table) {
            $table->id(); // Ganti dari uuid() ke id()
            $table->foreignId('flight_id')->constrained('flights');
            $table->foreignId('cro_id')->constrained('users');
            $table->foreignId('shift_id')->constrained('shifts');
            $table->dateTime('scheduled_fueling_time');
            $table->dateTime('actual_fueling_time')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();
            
            $table->index('scheduled_fueling_time');
        });
    }

    public function down()
    {
        Schema::dropIfExists('fuel_schedules');
    }
};