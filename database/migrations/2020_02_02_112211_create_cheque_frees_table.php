<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChequeFreesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cheque_schedules', function (Blueprint $table) {
            $table->string('type')->default(\App\Cheque::TYPE_SCHEDULE);
        });
        DB::statement("ALTER TABLE cheque_schedules CHANGE COLUMN user_id user_id  BIGINT(20) UNSIGNED NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cheque_frees');
    }
}
