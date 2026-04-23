<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('linkedin_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('text');                            // corpo do post (max 3000 chars LinkedIn)
            $table->string('image_path')->nullable();        // path no storage (disk: public) — opcional
            $table->string('image_disk')->default('public');
            $table->string('image_title')->nullable();       // título da imagem na UI do LinkedIn
            $table->enum('status', ['draft', 'scheduled', 'published', 'failed'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('linkedin_post_id')->nullable();  // urn retornado pela API
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_posts');
    }
};
