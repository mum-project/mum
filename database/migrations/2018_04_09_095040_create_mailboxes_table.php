<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailboxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailboxes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('local_part');
            $table->string('password');
            $table->rememberToken();
            $table->string('name')->nullable();
            $table->unsignedInteger('domain_id');
            $table->string('alternative_email')->nullable();
            $table->unsignedInteger('quota')->nullable();
            $table->string('homedir');
            $table->string('maildir');
            $table->boolean('is_super_admin')->default(false);
            $table->boolean('send_only')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade');
            $table->unique(['local_part', 'domain_id']);
            $table->unique('homedir');
            $table->unique('maildir');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mailboxes');
    }
}
