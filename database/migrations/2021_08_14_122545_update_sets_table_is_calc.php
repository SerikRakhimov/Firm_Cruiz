<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSetsTableIsCalc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sets', function (Blueprint $table) {
            $table->boolean('is_calc_sort')->default(false);
            $table->boolean('is_upd_count')->default(false);
            $table->boolean('is_upd_sum')->default(true);
            $table->boolean('is_upd_first')->default(false);
            $table->boolean('is_upd_last')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
