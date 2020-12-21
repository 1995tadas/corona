<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('summaries', function (Blueprint $table) {
            $table->id();
            $table->integer('new_confirmed');
            $table->integer('total_confirmed');
            $table->integer('new_deaths');
            $table->integer('total_deaths');
            $table->integer('new_recovered');
            $table->integer('total_recovered');
            $table->foreignId('country_id')->unique()->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('summaries');
    }
}
