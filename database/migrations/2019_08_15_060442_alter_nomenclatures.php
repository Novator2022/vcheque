<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNomenclatures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nomenclatures', function (Blueprint $table) {
            $table->dropColumn('amount');
            $table->dropForeign('organisation_id');
            $table->dropIndex('organisation_id');
            $table->dropColumn('organisation_id');
            $table->enum('nds',[0,10,20])->default(20);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nomenclatures', function (Blueprint $table) {
            $table->dropColumn('nds');
            $table->bigInteger('organisation_id')->unsigned()->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->numeric('amount',12,2);
        });
    }
}
