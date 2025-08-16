<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('journal_particulars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ledger_id')->nullable()->constrained();
            $table->string('date')->nullable();
            $table->date('en_date')->nullable();
            $table->double('dr_amount', 12, 2)->default(0);
            $table->double('cr_amount', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->boolean('is_cancelled')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('journal_particulars');
    }
};
