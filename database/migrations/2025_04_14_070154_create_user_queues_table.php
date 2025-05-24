<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserQueuesTable extends Migration
{
    public function up()
    {
        Schema::create('user_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->constrained('queues')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('queue_number');
            $table->date('queue_date');
            $table->timestamps();

            $table->unique(['user_id', 'queue_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_queues');
    }
}
