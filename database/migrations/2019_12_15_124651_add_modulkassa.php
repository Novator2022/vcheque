<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddModulkassa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cheques', function (Blueprint $table) {
            $table->json('modulkassa')->nullable();
        });
        Schema::table('organisations', function (Blueprint $table) {
            $table->boolean('modulkassa')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cheques', function (Blueprint $table) {
            //
        });
    }
}
