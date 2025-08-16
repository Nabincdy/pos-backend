<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('product_openings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('unit_id')->constrained();
            $table->double('rate', 12, 2)->default(0);
            $table->double('quantity', 12, 2)->default(0);
            $table->string('opening_date')->nullable();
            $table->date('en_opening_date')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->string('batch_no')->nullable();
            $table->string('expiry_date')->nullable();
            $table->date('en_expiry_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_openings');
    }
};
