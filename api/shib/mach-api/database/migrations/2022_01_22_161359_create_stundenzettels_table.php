<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStundenzettelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stundenzettels', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('personal_nummer');
            $table->integer('stundensatz');
            $table->integer('vereinbarte_arbeitszeit');
            $table->string('institut');
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
        Schema::dropIfExists('stundenzettels');
    }
}
