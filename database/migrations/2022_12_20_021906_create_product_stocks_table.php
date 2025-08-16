<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('expiry_date')->nullable();
            $table->date('en_expiry_date')->nullable();
            $table->string('batch_no')->nullable();
            $table->nullableMorphs('model');
            $table->foreignId('unit_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->double('quantity', 12, 2)->default(0);
            $table->double('rate', 12, 2)->default(0);
            $table->enum('type', ['In', 'Out'])->default('In');
            $table->string('date')->nullable();
            $table->date('en_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_stocks');
    }
};
