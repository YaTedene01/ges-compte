<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('operation_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable();
            $table->string('method', 10);
            $table->string('path');
            $table->longText('payload')->nullable();
            $table->integer('status')->default(200);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('operation_logs');
    }
};
