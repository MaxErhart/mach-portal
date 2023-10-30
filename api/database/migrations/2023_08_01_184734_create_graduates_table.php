<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGraduatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('graduates', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('MatrNr');
            $table->string('Vorname');
            $table->string('Nachname');
            $table->integer('Version');
            $table->string('Status');
            $table->boolean('Immatrikuliert');
            $table->date('Letzte Prüfung');
            $table->integer('Ist-LP');
            $table->integer('Soll-LP');
            $table->date('Studienstart');
            $table->string('Studienende');
            $table->date('Exma-Datum');
            $table->integer('Fachsemester');
            $table->string('E-Mail');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('graduates');
    }
}
