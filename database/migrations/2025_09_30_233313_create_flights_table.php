<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->string('flight_number');
            $table->string('airline_code');
            $table->string('departure_airport');
            $table->string('arrival_airport');
            $table->dateTime('scheduled_departure'); // STD untuk jadwal pengisian
            $table->string('status')->default('Scheduled');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index('scheduled_departure');
        });
    }

    public function down()
    {
        Schema::dropIfExists('flights');
    }
};