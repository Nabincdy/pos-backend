<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('bank_account')->nullable();
            $table->string('journal_voucher')->nullable();
            $table->string('payment_voucher')->nullable();
            $table->string('receipt_voucher')->nullable();
            $table->string('client_group')->nullable();
            $table->string('client')->nullable();
            $table->string('company')->nullable();
            $table->string('supplier')->nullable();
            $table->string('product')->nullable();
            $table->string('product_category')->nullable();
            $table->string('warehouse')->nullable();
            $table->string('purchase')->nullable();
            $table->string('purchase_return')->nullable();
            $table->string('sales')->nullable();
            $table->string('sales_return')->nullable();
            $table->string('employee')->nullable();
            $table->string('payable_charge')->nullable();
            $table->string('payslip')->nullable();
            $table->string('tax')->nullable();
            $table->string('supplier_payment')->nullable();
            $table->string('client_payment')->nullable();
            $table->string('quotation')->nullable();
        });
    }

    public function down()
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn('bank_account');
            $table->dropColumn('journal_voucher');
            $table->dropColumn('payment_voucher');
            $table->dropColumn('receipt_voucher');
            $table->dropColumn('client_group');
            $table->dropColumn('client');
            $table->dropColumn('company');
            $table->dropColumn('supplier');
            $table->dropColumn('product');
            $table->dropColumn('product_category');
            $table->dropColumn('warehouse');
            $table->dropColumn('purchase');
            $table->dropColumn('purchase_return');
            $table->dropColumn('sales');
            $table->dropColumn('sales_return');
            $table->dropColumn('employee');
            $table->dropColumn('payable_charge');
            $table->dropColumn('payslip');
            $table->dropColumn('tax');
            $table->dropColumn('supplier_payment');
            $table->dropColumn('client_payment');
        });
    }
};
