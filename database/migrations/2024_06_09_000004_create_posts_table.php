<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id('posts_id');
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('content');
            $table->dateTime('created_time');
            $table->unsignedBigInteger('category_id');
            $table->dateTime('updated_time')->nullable();
            $table->unsignedBigInteger('tag_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
}; 