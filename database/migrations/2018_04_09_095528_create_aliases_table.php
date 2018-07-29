<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAliasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aliases', function (Blueprint $table) {
            $table->increments('id');
            $table->string('local_part');
            $table->string('description')->nullable();
            $table->unsignedInteger('domain_id');
            $table->boolean('active')->default(true);
            $table->timestamp('deactivate_at')->nullable();
            $table->timestamps();

            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade');
            $table->unique(['local_part', 'domain_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aliases');
    }
}
