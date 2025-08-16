<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('pay_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('ledger_id')->nullable()->constrained();
            $table->enum('type', ['Addition', 'Deduction'])->default('Addition');
            $table->boolean('is_taxable')->default(0);
            $table->foreignId('tax_id')->nullable()->constrained('taxes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pay_heads');
    }
};
