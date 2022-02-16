<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutorisationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autorisations', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->boolean('access_product')->default(true);
            $table->boolean('product_list')->default(true);
            $table->boolean('product_add')->default(true);
            $table->boolean('product_update')->default(true);
            $table->boolean('stock_add')->default(true);
            $table->boolean('stock_update')->default(true);
            $table->boolean('operations_list')->default(true);
            $table->boolean('operations_view')->default(true);
            $table->boolean('charge_list')->default(true);
            $table->boolean('charge_add')->default(true);
            $table->boolean('charge_update')->default(true);
            $table->boolean('counter_discount')->default(true);
            $table->boolean('counter_return')->default(true);
            $table->boolean('counter_synthesis')->default(true);
            $table->boolean('corbeille')->default(true);
            $table->boolean('dashboard')->default(true);
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
        Schema::dropIfExists('autorisations');
    }
}
