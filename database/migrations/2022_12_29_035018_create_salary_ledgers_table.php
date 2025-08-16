<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('salary_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained();
            $table->foreignId('employee_id')->constrained();
            $table->foreignId('payable_charge_id')->nullable()->constrained()->cascadeOnDelete();
            $table->nullableMorphs('model');
            $table->foreignId('month_id')->nullable()->constrained();
            $table->string('date')->nullable();
            $table->double('dr_amount', 12, 2)->default(0);
            $table->double('cr_amount', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('create_user_id')->constrained('users');
            $table->boolean('is_cancelled')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_ledgers');
    }
};
