<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cleanings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_boy_id')->constrained();
            $table->unsignedBigInteger('room_id')->constrained();
            $table->dateTime('suppose_to_start')->nullable();
            $table->dateTime('suppose_to_end')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finish_at')->nullable();
            $table->boolean('delayed')->nullable();
            $table->boolean('unassigned')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cleanings');
    }
};
