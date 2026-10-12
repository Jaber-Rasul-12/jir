<?php namespace Car\Car\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateCarCarCarsSaleContracts extends Migration
{
    public function up()
    {
        Schema::create('car_car_cars_sale_contracts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('car_id')->unsigned();
            $table->integer('sale_contract_id')->unsigned();
            $table->primary(['car_id', 'sale_contract_id']);

            $table->foreign('car_id')
                    ->references('id')
                    ->on('car_car_cars')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

            $table->foreign('sale_contract_id')
                    ->references('id')
                    ->on('car_car_sale_contracts')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('car_car_cars_sale_contracts');
    }
}