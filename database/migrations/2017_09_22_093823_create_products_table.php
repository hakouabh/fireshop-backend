<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_types', function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
            $table->unsignedBigInteger('company_id')->index();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->softDeletes();
            $table->timestamps();

        });

        Schema::create('products', function (Blueprint $table) {
            $table->id('id');
            $table->string('sku')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('type_id')->index();
            $table->foreign('type_id')->references('id')->on('product_types');
            $table->unsignedBigInteger('company_id')->index();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('site_id')->index();
            $table->foreign('site_id')->references('id')->on('sites');
            $table->integer('stock');
            $table->double('cost');
            $table->double('selling_price');
            $table->text('image')->nullable();
            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_types');
    }
}
