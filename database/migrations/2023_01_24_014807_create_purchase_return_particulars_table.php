<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('purchase_return_particulars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('unit_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->double('quantity', 10, 2)->default(0);
            $table->double('rate', 12, 2)->default(0);
            $table->foreignId('purchase_tax_id')->nullable()->constrained('taxes');
            $table->double('purchase_tax_amount', 12, 2)->default(0);
            $table->double('discount_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_return_particulars');
    }
};
