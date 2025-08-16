<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('email');
            $table->string('phone')->nullable()->after('photo');
            $table->timestamp('status_at')->nullable()->after('phone');
            $table->foreignId('role_id')->constrained();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('photo', 'phone', 'status_at');
            $table->dropForeign('role_id');
        });
    }
};
