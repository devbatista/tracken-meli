<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportadorasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('transportadoras')) {
            return false;
        }
        
        Schema::create('transportadoras', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->string('cnpj', 18)->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transportadoras');
    }
}
