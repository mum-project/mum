<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAliasRequestRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alias_request_recipients', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('request_id');
            $table->string('recipient_address');
            $table->unsignedInteger('mailbox_id')->nullable();

            $table->unique(['request_id', 'recipient_address']);
            $table->foreign('request_id')->references('id')->on('alias_requests')->onDelete('cascade');
            $table->foreign('mailbox_id')->references('id')->on('mailboxes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alias_request_recipients');
    }
}
