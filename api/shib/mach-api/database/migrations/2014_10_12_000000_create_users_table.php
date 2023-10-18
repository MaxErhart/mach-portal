<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;


class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('shib_id');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable()->default(new Expression('NULL'));
            $table->rememberToken();
            $table->timestamps();
            $table->json('affiliation')->nullable()->default(new Expression('(JSON_ARRAY())'));
            $table->integer('degree')->nullable()->default(new Expression('NULL'));
            $table->string('degreeText')->nullable()->default(new Expression('NULL'));
            $table->integer('fieldOfStudy')->nullable()->default(new Expression('NULL'));
            $table->integer('fieldOfStudyId')->nullable()->default(new Expression('NULL'));
            $table->integer('matriculationNumber')->nullable()->default(new Expression('NULL'));
            $table->string('fieldOfStudyText')->nullable()->default(new Expression('NULL'));         
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
