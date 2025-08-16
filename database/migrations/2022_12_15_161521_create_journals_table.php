<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained();
            $table->nullableMorphs('model');
            $table->string('journal_no');
            $table->string('date')->nullable();
            $table->date('en_date')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->text('remarks')->nullable();
            $table->boolean('is_cancelled')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('journals');
    }
};
