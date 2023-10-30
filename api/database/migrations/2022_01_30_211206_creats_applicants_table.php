<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreatsApplicantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->string('sex');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email');
            $table->date('date_of_birth');
            $table->string('street');
            $table->string('street_number');
            $table->string('zipcode');
            $table->string('city');
            $table->string('country');
            $table->integer('aplicant_number');
            $table->integer('admission_number');
            $table->string('degree');
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
        Schema::dropIfExists('applicants');
    }
}
