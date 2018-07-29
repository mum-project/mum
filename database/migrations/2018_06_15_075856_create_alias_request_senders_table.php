<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAliasRequestSendersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alias_request_senders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mailbox_id');
            $table->unsignedInteger('request_id');

            $table->unique(['mailbox_id', 'request_id']);
            $table->foreign('mailbox_id')->references('id')->on('mailboxes')->onDelete('cascade');
            $table->foreign('request_id')->references('id')->on('alias_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alias_request_senders');
    }
}
