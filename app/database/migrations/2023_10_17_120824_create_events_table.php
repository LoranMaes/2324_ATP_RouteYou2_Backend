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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->decimal('price');
            $table->unsignedInteger('max_participant');
            $table->string('city');
            $table->integer('zip');
            $table->string('street');
            $table->string('house_number', 10);
            $table->boolean('visible');
            $table->string('image');
            $table->enum('type', ['GENERAL', 'CLUBEVENT', 'ROUTEBUDDY', 'WEBINAR']);
            $table->decimal('latitude', 8, 6);
            $table->decimal('longitude', 9, 6);
            $table->foreignId('organisation_id')->constrained('organisations')->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained('badges')->cascadeOnDelete();
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
        Schema::dropIfExists('events');
    }
};
