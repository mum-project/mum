<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAliasSendersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alias_senders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mailbox_id');
            $table->unsignedInteger('alias_id');
            $table->timestamps();

            $table->foreign('mailbox_id')->references('id')->on('mailboxes')->onDelete('cascade');
            $table->foreign('alias_id')->references('id')->on('aliases')->onDelete('cascade');
            $table->unique(['mailbox_id', 'alias_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alias_senders');
    }
}
