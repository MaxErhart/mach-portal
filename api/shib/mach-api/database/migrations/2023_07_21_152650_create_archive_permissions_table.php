<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_permissions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId("user_id")->references('id')->on('users')->onDelete('cascade');
            $table->integer('permission');
            $table->string('directory');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archive_permissions');
    }
}
