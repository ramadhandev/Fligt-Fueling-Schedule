<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id(); // Ganti dari uuid() ke id()
            $table->string('name'); // Pagi, Siang, Malam
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('pengawas_id')->constrained('users'); // Gunakan foreignId
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shifts');
    }
};