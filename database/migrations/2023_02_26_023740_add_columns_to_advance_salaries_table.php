<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('advance_salaries', function (Blueprint $table) {
            $table->string('payment_method');
            $table->foreignId('cash_bank_ledger_id')->nullable()->constrained('ledgers')->cascadeOnDelete();
            $table->foreignId('create_user_id')->constrained('users');
            $table->boolean('is_cancelled')->default(0);
            $table->string('cancelled_reason')->nullable();
            $table->foreignId('cancel_user_id')->nullable()->constrained('users');
        });
    }

    public function down()
    {
        Schema::table('advance_salaries', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->dropConstrainedForeignId('cash_bank_ledger_id');
            $table->dropConstrainedForeignId('create_user_id');
            $table->dropColumn('is_cancelled');
            $table->dropColumn('cancelled_reason');
            $table->dropConstrainedForeignId('cancel_user_id');
        });
    }
};
