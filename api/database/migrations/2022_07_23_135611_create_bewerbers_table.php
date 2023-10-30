<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBewerbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bewerbers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('Geschlecht');
            $table->string('Name');
            $table->string('Vorname');
            $table->date('Geboren');
            $table->integer('Bewerbungs-nummer');
            $table->string('Adresse');
            $table->string('Ort');
            $table->string('Land');
            $table->string('Datum Antrag');
            $table->string('KIT-E-Mail');
            $table->string('Bemerkung');
            $table->string('Studiengang');
            $table->string('ILIAS');
            $table->string('Ergebnis');
            $table->date('last_login');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bewerbers');
    }
}
