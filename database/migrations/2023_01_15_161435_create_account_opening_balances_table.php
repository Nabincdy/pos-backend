<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('account_opening_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained();
            $table->foreignId('ledger_id')->constrained()->cascadeOnDelete();
            $table->double('dr_amount', 12, 2)->default(0);
            $table->double('cr_amount', 12, 2)->default(0);
            $table->string('opening_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_opening_balances');
    }
};
