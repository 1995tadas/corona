<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('country')->unique();
            $table->string('slug')->unique();
            $table->char('iso2', 2)->unique();
            $table->foreignId('region_id')->nullable()->constrained();
            $table->foreignId('sub_region_id')->nullable()->constrained();
            $table->string('capital')->nullable();
            $table->integer('population')->nullable();
            $table->integer('area')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('countries');
    }
}
