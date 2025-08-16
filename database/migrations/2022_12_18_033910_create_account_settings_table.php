<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('account_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_ledger_id')->nullable()->constrained('ledgers');
            $table->foreignId('bank_ledger_group_id')->nullable()->constrained('ledger_groups');
            $table->foreignId('supplier_ledger_group_id')->nullable()->constrained('ledger_groups');
            $table->foreignId('client_ledger_group_id')->nullable()->constrained('ledger_groups');
            $table->foreignId('tax_ledger_group_id')->nullable()->constrained('ledger_groups');
            $table->foreignId('purchase_ledger_id')->nullable()->constrained('ledgers');
            $table->foreignId('sales_ledger_id')->nullable()->constrained('ledgers');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_settings');
    }
};
