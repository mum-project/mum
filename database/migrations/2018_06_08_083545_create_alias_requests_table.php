<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAliasRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alias_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mailbox_id');
            $table->unsignedInteger('domain_id');
            $table->unsignedInteger('alias_id')->nullable();
            $table->string('local_part');
            $table->string('description')->nullable();
            $table->enum('status', ['open', 'approved', 'dismissed'])->default('open');
            $table->timestamps();

            $table->foreign('mailbox_id')->references('id')->on('mailboxes')->onDelete('cascade');
            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade');
            $table->foreign('alias_id')->references('id')->on('aliases')->onDelete('cascade');

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
        Schema::dropIfExists('alias_requests');
    }
}
