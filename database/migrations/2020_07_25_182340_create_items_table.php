<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::defaultStringLength(768);
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('base_id')->default(0);
            // размер строкового поля 1000 также прописан в ItemController::calc_value_func()
            $table->string('code',255)->default("");
            $table->string('name_lang_0',1000)->default("");
            $table->string('name_lang_1',1000)->default("");
            $table->string('name_lang_2',1000)->default("");
            $table->string('name_lang_3',1000)->default("");
            $table->timestamps();
            $table->index('base_id');
            $table->unique(['base_id', 'code']);
            //$table->index('name_lang_0');
            //$table->index('name_lang_1');
            //$table->index('name_lang_2');
            //$table->index('name_lang_3');
//          $table->unique(['base_id', 'name_lang_0']);
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
        Schema::dropIfExists('items');
    }
}
