<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->string('dob')->nullable();
            $table->integer('rank');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo')->nullable();
            $table->foreignId('department_id')->nullable()->constrained();
            $table->foreignId('designation_id')->nullable()->constrained();
            $table->string('joining_date')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('citizenship_no')->nullable();
            $table->string('pan_no')->nullable();
            $table->string('signature')->nullable();
            $table->string('address')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
