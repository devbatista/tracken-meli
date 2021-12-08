<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCredentialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('credentials')) {
            return false;
        }

        Schema::create('credentials', function (Blueprint $table) {
            $table->increments('id');
            $table->string('access_token')->nullable();
            $table->string('app_id');
            $table->string('client_secret');
            $table->string('redirect_uri');
            $table->string('code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credential');
    }
}
