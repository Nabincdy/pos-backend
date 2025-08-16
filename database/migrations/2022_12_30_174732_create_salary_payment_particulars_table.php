<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('salary_payment_particulars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payable_charge_id')->nullable()->constrained()->cascadeOnDelete();
            $table->nullableMorphs('model');
            $table->double('amount', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->boolean('is_cancelled')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_payment_particulars');
    }
};
