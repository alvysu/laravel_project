<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id('file_id');
            $table->string('user_id');
            $table->string('file_path');
            $table->dateTime('upload_time');
            $table->string('post_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
}; 