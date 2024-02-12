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
        Schema::create('participations', function (Blueprint $table) {
            $table->id();
            $table->boolean('paid');
            $table->boolean('present');
            $table->enum('reaction', ['GOING', 'INTERESTED', 'ABSENT']);
            $table->string("qr_code");
            $table->string('checkout_url')->nullable();
            $table->string('club_name')->nullable();
            $table->boolean('carpool');
            $table->enum('carpool_role', ['DRIVER', 'PASSENGER'])->nullable();
            $table->text('problem')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('badge_id')->nullable()->constrained('badges')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('participations');
    }
};
