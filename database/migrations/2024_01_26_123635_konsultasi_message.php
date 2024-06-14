<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Konsultasi_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konsultasi_id');
            $table->text('message');
            $table->enum('role', ['user', 'admin', 'psikolog'])->default('psikolog');
            $table->string('sender');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Konsultasi_messages');
    }
};
