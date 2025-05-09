<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->string('logo')->nullable();
            $table->string('email')->nullable();
            $table->string('time_zone')->default('America/Guatemala');
            $table->string('currency')->default('GTQ Q');
            $table->string('currency_simbol')->default('Q');
            $table->string('currency_iso')->default('GTQ');
            $table->string('fb_link')->nullable();
            $table->string('inst_link')->nullable();
            $table->string('yt_link')->nullable();
            $table->string('wapp_link')->nullable();
            $table->decimal('descuento_maximo', 8, 2)->default(0.00);
            $table->decimal('impuesto', 8, 2)->default(0.00);
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
        Schema::dropIfExists('configs');
    }
}
