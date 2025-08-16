<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_name');
            $table->string('code');
            $table->string('phone')->nullable();
            $table->foreignId('ledger_id')->nullable()->constrained();
            $table->string('email')->nullable();
            $table->string('profile_photo')->nullable();
            $table->foreignId('company_id')->nullable()->constrained();
            $table->string('pan_no')->nullable();
            $table->string('address')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('suppliers');
    }
};
