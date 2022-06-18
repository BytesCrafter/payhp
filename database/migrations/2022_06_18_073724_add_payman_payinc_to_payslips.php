<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymanPayincToPayslips extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->string('payman', 100)->nullable()->after('group_id');
            $table->string('payinc', 100)->nullable()->after('group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn('payinc');
            $table->dropColumn('payman');
        });
    }
}
