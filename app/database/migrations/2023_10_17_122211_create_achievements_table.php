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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->boolean('completed');
            $table->foreignId('checkpoint_id')->constrained('checkpoints')->cascadeOnDelete();
            $table->foreignId('participation_id')->constrained('participations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('achievements');
    }
};
