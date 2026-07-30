<?php namespace Car\Car\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateCarCarCars extends Migration
{
    public function up()
    {
        Schema::create('car_car_cars', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('ownership');
            $table->string('type')->nullable();
            $table->integer('model_id')->unsigned();
            $table->text('chassis_number');
            $table->integer('brand_id')->unsigned();
            $table->date('year_of_manufacturing_date');
            $table->string('fuel_type');
            $table->text('license_plate_number')->nullable();
            $table->integer('country_id')->unsigned()->nullable();
            $table->text('license_plate_number_new')->nullable();
            $table->integer('country_new_id')->unsigned()->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('brand_id')
                    ->references('id')
                    ->on('car_car_brands')
                    ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('model_id')
                    ->references('id')
                    ->on('car_car_models')
                    ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('country_id')
                    ->references('id')
                    ->on('car_car_countries')
                    ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('country_new_id')
                    ->references('id')
                    ->on('car_car_countries')
                    ->onDelete('cascade')->onUpdate('cascade');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('car_car_cars');
    }
}
