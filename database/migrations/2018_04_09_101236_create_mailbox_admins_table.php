<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailboxAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailbox_admins', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('admin_mailbox_id');
            $table->unsignedInteger('mailbox_id');
            $table->timestamps();

            $table->foreign('admin_mailbox_id')->references('id')->on('mailboxes')->onDelete('cascade');
            $table->foreign('mailbox_id')->references('id')->on('mailboxes')->onDelete('cascade');
            $table->unique(['admin_mailbox_id', 'mailbox_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mailbox_admins');
    }
}
