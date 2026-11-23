<?php namespace Finance\Finance\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateFinanceFinanceBudgets extends Migration
{
    public function up()
    {
        Schema::create('finance_finance_budgets', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('type_id')->unsigned();
            $table->string('currency');
            $table->double('amount', 10, 0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();  
            $table->foreign('type_id')
                        ->references('id')
                        ->on('finance_finance_types')
                        ->onDelete('cascade')->onUpdate('cascade');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('finance_finance_budgets');
    }
}
