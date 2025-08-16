<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained();
            $table->string('name');
            $table->string('code');
            $table->string('sku')->nullable();
            $table->string('product_type')->nullable();
            $table->integer('reorder_quantity')->default(0);
            $table->string('barcode')->nullable();
            $table->foreignId('unit_id')->constrained();
            $table->foreignId('brand_id')->nullable()->constrained();
            $table->double('purchase_rate', 12, 2)->default(0);
            $table->foreignId('purchase_tax_id')->nullable()->constrained('taxes');
            $table->double('sales_rate', 12, 2)->default(0);
            $table->foreignId('sales_tax_id')->nullable()->constrained('taxes');
            $table->string('image')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
