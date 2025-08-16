<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained();
            $table->string('invoice_no');
            $table->string('purchase_date')->nullable();
            $table->date('en_purchase_date')->nullable();
            $table->string('payment_type');
            $table->foreignId('supplier_ledger_id')->constrained('ledgers');
            $table->foreignId('create_user_id')->constrained('users');
            $table->boolean('is_cancelled')->default(0);
            $table->string('cancelled_reason')->nullable();
            $table->foreignId('cancel_user_id')->nullable()->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
};
