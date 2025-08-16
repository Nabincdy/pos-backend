<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained();
            $table->foreignId('purchase_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('invoice_no');
            $table->string('return_date')->nullable();
            $table->date('en_return_date')->nullable();
            $table->foreignId('supplier_ledger_id')->constrained('ledgers');
            $table->foreignId('create_user_id')->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_returns');
    }
};
