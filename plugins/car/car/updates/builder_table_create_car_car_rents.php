<?php namespace Car\Car\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateCarCarRents extends Migration
{
    public function up()
    {
        Schema::create('car_car_rents', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('car_id')->unsigned();
            $table->integer('customer_owner_id')->unsigned();
            $table->integer('customer_tenant_id')->unsigned();
            $table->date('start_date');
            $table->date('end_date');
            $table->double('watch_price', 10, 0);
            $table->double('rent_allowance', 10, 0);
            $table->double('additional_rent_amount', 10, 0);
            $table->string('insurance_number');
            $table->double('the_second_team_paid_for_any_damage', 10, 0);
            $table->integer('customer_bail_id')->unsigned();
            $table->string('name_first_witness');
            $table->string('name_second_witness');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('car_id')
                    ->references('id')
                    ->on('car_car_cars')
                    ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('customer_owner_id')
                    ->references('id')
                    ->on('car_car_customers')
                    ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('customer_tenant_id')
                    ->references('id')
                    ->on('car_car_customers')
                    ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('customer_bail_id')
                    ->references('id')
                    ->on('car_car_customers')
                    ->onDelete('cascade')->onUpdate('cascade');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('car_car_rents');
    }
}
