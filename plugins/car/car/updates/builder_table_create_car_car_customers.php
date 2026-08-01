<?php namespace Car\Car\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateCarCarCustomers extends Migration
{
    public function up()
    {
        Schema::create('car_car_customers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('full_name');
            $table->text('id_number')->nullable();
            $table->string('address');
            $table->text('phone');
            $table->text('driving_license')->nullable();
            $table->text('type_of_drivers_license')->nullable();
            $table->date('date_of_drivers_license')->nullable();
            $table->date('valid_for_the_end')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('car_car_customers');
    }
}
