<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGraduateThesesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('graduate_theses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('MatNr');
            $table->string('Vorname');
            $table->string('Nachname');
            $table->float('Note');
            $table->string('Prüfer');
            $table->string('Zweit-Prüfer');
            $table->string('Status');
            $table->date('Abgabedatum (geplant)');
            $table->date('Abgabedatum (erfolgt)');
            $table->date('Vortragsdatum');
            $table->date('Prüfungsdatum');
            $table->string('Nummer');
            $table->string('Interne Kennung');
            $table->string('Startsemester');
            $table->string('E-Mail');
            $table->string('Teilleistung');
            $table->string('Titel');
            $table->string('Titel übersetzt');
            $table->string('Sprache');
            $table->date('Vergabedatum');
            $table->date('Korrekturfrist');
            $table->boolean('Externe Arbeit');
            $table->string('Prüfungsbearbeiter');
            $table->string('Organisationseinheiten');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('graduate_theses');
    }
}
