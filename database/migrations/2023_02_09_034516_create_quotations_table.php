<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained();
            $table->string('invoice_no');
            $table->string('quotation_date')->nullable();
            $table->date('en_quotation_date')->nullable();
            $table->foreignId('client_ledger_id')->constrained('ledgers');
            $table->foreignId('create_user_id')->constrained('users');
            $table->boolean('is_converted_to_sale')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quotations');
    }
};
