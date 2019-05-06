<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('push_events', function (Blueprint $table) {
            $pushers = ['2_O_CLOCK', '4_O_CLOCK'];

            $table->bigIncrements('id');
            $table->bigInteger('user_id', false, true);
            $table->enum('pusher', $pushers);
            $table->integer('pushed_times');
            $table->string('latitude')->nullable(true);
            $table->string('longitude')->nullable(true);
            $table->timestamps();
        });

        Schema::table('push_events', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('push_events');
    }
}
