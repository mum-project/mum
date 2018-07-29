<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_parameters', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('integration_id');
            $table->string('option')->nullable();       //      > command {option} "{value}"
            $table->string('value');                    // eg.  > ls -la "/etc"
            $table->boolean('use_equal_sign')->default(false);
            $table->timestamps();

            $table->foreign('integration_id')->references('id')->on('integrations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integration_parameters');
    }
}
