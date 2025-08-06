<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mail_id');
            $table->unsignedBigInteger('sender_id')->nullable(); // Permitir null para sender_id
            $table->unsignedBigInteger('recipient_id');
            $table->string('sender_type'); // 'client' o 'admin'
            $table->text('message');
            $table->timestamps();

            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
         
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mail_messages');
    }
}