<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTlsPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tls_policies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('domain');
            $table->enum('policy', [
                'none',
                'may',
                'encrypt',
                'dane',
                'dane-only',
                'fingerprint',
                'verify',
                'secure'
            ]);
            $table->text('params')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique('domain');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tls_policies');
    }
}
