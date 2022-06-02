<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayslipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->nullable();
            $table->uuid('group_id')->nullable();

            $table->string('fullname', 50)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('title', 50)->nullable();
            $table->string('department', 50)->nullable();
            $table->string('directorate', 50)->nullable();

            $table->string('bankname', 25)->nullable();
            $table->string('banknum', 15)->nullable();

            $table->string('payriod', 50)->nullable();
            $table->string('paydate', 50)->nullable();

            $table->float('monthly', 8, 2)->default(0.00);
            $table->float('allowance', 8, 2)->default(0.00);
            $table->float('deduction', 8, 2)->default(0.00);
            $table->float('incentive', 8, 2)->default(0.00);
            $table->float('nightdiff', 8, 2)->default(0.00);

            $table->float('reghdpay', 8, 2)->default(0.00);
            $table->float('spchdpay', 8, 2)->default(0.00);
            $table->float('regot', 8, 2)->default(0.00);
            $table->float('rstot', 8, 2)->default(0.00);
            $table->float('reghdot', 8, 2)->default(0.00);
            $table->float('spchdot', 8, 2)->default(0.00);

            $table->float('tax', 8, 2)->default(0.00);
            $table->float('sss', 8, 2)->default(0.00);
            $table->float('phealth', 8, 2)->default(0.00);
            $table->float('pagibig', 8, 2)->default(0.00);
            $table->float('hmo', 8, 2)->default(0.00);
            $table->float('other', 8, 2)->default(0.00);

            $table->bigInteger('generated_by')->nullable();
            $table->timestamp('emailed_at', $precision = 0);
            $table->timestamps();
            $table->softDeletes();
            $table->string('status');

            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payslips');
    }
}
