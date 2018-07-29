<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDomainAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domain_admins', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mailbox_id');
            $table->unsignedInteger('domain_id');
            $table->timestamps();

            $table->foreign('mailbox_id')->references('id')->on('mailboxes')->onDelete('cascade');
            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade');
            $table->unique(['mailbox_id', 'domain_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domain_admins');
    }
}
