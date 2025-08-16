<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('ledger_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_head_id')->nullable()->constrained();
            $table->foreignId('ledger_group_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('group_name');
            $table->string('code');
            $table->boolean('auto_generated')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ledger_groups');
    }
};
