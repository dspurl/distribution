<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistributionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distribution_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->default(0)->index()->comment('用户ID');
            $table->integer('children_id')->default(0)->comment('返佣触发人ID');
            $table->string('name',30)->nullable()->comment('分销名称');
            $table->tinyInteger('type')->default(0)->comment('返佣方式0按固定值1按比例');
            $table->tinyInteger('level')->default(1)->comment('级别1：一级2：二级3：三级	');
            $table->integer('price')->default(0)->comment('返佣金额');
            $table->tinyInteger('state')->default(0)->comment('分销状态, 0触发1成功, 只有成功的分销记录才可提现');
            $table->timestamps();
            $table->charset = 'utf8';
            $table->engine = 'InnoDB';
            $table->collation = 'utf8_general_ci';
            $table->unique('id');
        });
        DB::statement("ALTER TABLE `distribution_logs` COMMENT='分销记录'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('distribution_logs');
    }
}
