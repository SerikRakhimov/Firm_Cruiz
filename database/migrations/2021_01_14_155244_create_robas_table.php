<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRobasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('robas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->default(0);
            $table->unsignedBigInteger('base_id')->default(0);
            $table->boolean('is_inlist')->default(false);
            $table->boolean('is_create')->default(false);
            $table->boolean('is_read')->default(false);
            $table->boolean('is_update')->default(false);
            $table->boolean('is_delete')->default(false);
            $table->boolean('is_byuser')->default(false);
            $table->timestamps();
            $table->index('role_id');
            $table->index('base_id');
            $table->unique(['role_id', 'base_id']);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('base_id')->references('id')->on('bases')->onDelete('cascade');
        });




    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('robas');
    }
}
