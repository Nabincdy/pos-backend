<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('account_settings', function (Blueprint $table) {
            $table->foreignId('salary_payable_ledger_id')->nullable()->constrained('ledgers');
        });
    }

    public function down()
    {
        Schema::table('account_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('salary_payable_ledger_id');
        });
    }
};
