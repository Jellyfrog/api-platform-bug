<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Minimal tables to reproduce API Platform bugs with custom primary keys.
 *
 * Uses the common Eloquent convention of <table>_id as primary key,
 * which is widespread in legacy databases.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('device_id');
            $table->string('hostname');
            $table->timestamps();
        });

        Schema::create('ports', function (Blueprint $table) {
            $table->increments('port_id');
            $table->unsignedInteger('device_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('device_id')->references('device_id')->on('devices');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ports');
        Schema::dropIfExists('devices');
    }
};
