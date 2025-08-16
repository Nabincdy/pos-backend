<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('receipt_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained();
            $table->string('invoice_no')->nullable();
            $table->foreignId('client_ledger_id')->nullable()->constrained('ledgers');
            $table->string('payment_method');
            $table->foreignId('cash_bank_ledger_id')->nullable()->constrained('ledgers')->cascadeOnDelete();
            $table->string('receipt_date')->nullable();
            $table->date('en_receipt_date')->nullable();
            $table->double('amount', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('create_user_id')->constrained('users');
            $table->boolean('is_cancelled')->default(0);
            $table->string('cancelled_reason')->nullable();
            $table->foreignId('cancel_user_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('receipt_records');
    }
};
