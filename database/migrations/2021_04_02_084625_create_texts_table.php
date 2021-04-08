<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('texts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id')->default(0);
            $table->text('name_lang_0')->default('');
            $table->text('name_lang_1')->default('');
            $table->text('name_lang_2')->default('');
            $table->text('name_lang_3')->default('');
            $table->timestamps();
            $table->unique('item_id');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('texts');
    }
}
