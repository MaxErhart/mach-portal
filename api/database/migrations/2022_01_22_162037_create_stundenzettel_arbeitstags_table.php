<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStundenzettelArbeitstagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stundenzettel_arbeitstags', function (Blueprint $table) {
            $table->id();
            $table->integer('stundenzettel_id');
            $table->date('start');
            $table->date('end');
            $table->string('task');
            $table->integer('vacation_millsec');
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
        Schema::dropIfExists('stundenzettel_arbeitstags');
    }
}
