<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAliasRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alias_recipients', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('alias_id');
            $table->string('recipient_address');
            $table->unsignedInteger('mailbox_id')->nullable();
            $table->timestamps();

            $table->unique(['alias_id', 'recipient_address']);
            $table->foreign('alias_id')->references('id')->on('aliases')->onDelete('cascade');
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
        Schema::dropIfExists('alias_recipients');
    }
}
