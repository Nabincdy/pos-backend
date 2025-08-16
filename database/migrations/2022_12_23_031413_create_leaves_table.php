<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->foreignId('leave_type_id')->constrained();
            $table->string('issued_date')->nullable();
            $table->date('en_issued_date')->nullable();
            $table->string('start_date')->nullable();
            $table->date('en_start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->date('en_end_date')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->default('Pending');
            $table->foreignId('submit_user_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leaves');
    }
};
