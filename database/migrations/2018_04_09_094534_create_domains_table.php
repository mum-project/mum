<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->increments('id');
            $table->string('domain');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('quota')->nullable();
            $table->unsignedBigInteger('max_quota')->nullable();
            $table->unsignedInteger('max_aliases')->nullable();
            $table->unsignedInteger('max_mailboxes')->nullable();
            $table->string('homedir');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique('domain');
            $table->unique('homedir');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domains');
    }
}
