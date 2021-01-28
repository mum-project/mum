<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropUnusedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('service_health_checks');
        Schema::drop('system_services');
        Schema::drop('alias_request_recipients');
        Schema::drop('alias_request_senders');
        Schema::drop('alias_requests');
        Schema::drop('integration_parameters');
        Schema::drop('integrations');
    }
}
