<?php namespace Car\Car\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateCarCarSaleContracts extends Migration
{
    public function up()
    {
        Schema::create('car_car_sale_contracts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('contract_number')->unique();
            $table->date('contract_date');
            $table->string('contract_place')->nullable();

            // بيانات البائع
            $table->string('seller_name');
            $table->string('seller_id_number');
            $table->string('seller_id_issuer')->nullable();
            $table->string('seller_address')->nullable();
            $table->string('seller_phone')->nullable();

            // بيانات المشتري
            $table->string('buyer_name');
            $table->string('buyer_id_number');
            $table->string('buyer_id_issuer')->nullable();
            $table->string('buyer_address')->nullable();
            $table->string('buyer_phone')->nullable();

            // الثمن
            $table->decimal('total_price', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->string('payment_terms')->nullable();

            // الشروط والأحكام
            $table->text('general_terms')->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('car_car_sale_contracts');
    }
}