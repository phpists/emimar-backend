<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file_entries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Назва файлу або папки');
            $table->enum('type', ['file', 'folder'])->comment('Тип');
            $table->bigInteger('parent_id')->nullable()->comment('Вказує на батьківську директорію');
            $table->bigInteger('project_id')->comment('ID проєкту');
            $table->string('path')->comment('Шлях до файлу в файловій системі (тільки для файлів)');
            $table->string('mime_type')->nullable()->comment('MIME тип (тільки для файлів)');
            $table->integer('size')->nullable()->comment('розмір файлу в байтах');
            $table->bigInteger('pos')->comment('Позиція');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_entries');
    }
};
