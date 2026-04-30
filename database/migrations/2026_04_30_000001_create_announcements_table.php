<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('category', ['important', 'info', 'schedule', 'result'])->default('info');
            $table->text('content');
            $table->string('link_text')->nullable();
            $table->string('link_url')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'publish_at']);
            $table->index(['is_pinned', 'publish_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
