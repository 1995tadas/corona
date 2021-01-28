<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoronasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coronas', function (Blueprint $table) {
            $table->id();
            $table->integer('confirmed');
            $table->integer('new_confirmed');
            $table->integer('deaths');
            $table->integer('new_deaths');
            $table->integer('active');
            $table->integer('new_active');
            $table->integer('recovered');
            $table->integer('new_recovered');
            $table->date('date');
            $table->foreignId('country_id');
            $table->unique(['country_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coronas');
    }
}
