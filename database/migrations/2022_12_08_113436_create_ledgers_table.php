<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_group_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('ledger_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('ledger_name');
            $table->string('code');
            $table->string('category')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('pan_no')->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('auto_generated')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ledgers');
    }
};
