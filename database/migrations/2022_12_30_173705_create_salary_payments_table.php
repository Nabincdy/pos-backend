<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payslip_no');
            $table->foreignId('fiscal_year_id')->constrained();
            $table->foreignId('month_id')->constrained();
            $table->string('payment_date')->nullable();
            $table->date('en_payment_date')->nullable();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method');
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
        Schema::dropIfExists('salary_payments');
    }
};
