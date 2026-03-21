<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('short_description');
            $table->longText('content_html');
            $table->string('icon_name')->default('circle');
            $table->string('category', 32); // consultoria | software | cloud
            $table->json('seo');
            $table->unsignedInteger('order')->default(0);
            $table->string('status', 16)->default('draft'); // draft | published
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
