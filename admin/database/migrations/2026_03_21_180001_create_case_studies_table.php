<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_studies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('status', 16)->default('draft'); // draft | published
            $table->boolean('featured')->default(false);
            $table->string('title');
            $table->string('customer_name');
            $table->string('sector');
            $table->text('short_summary');
            $table->longText('content_html');
            $table->json('metrics');
            $table->json('main_image');
            $table->json('seo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_studies');
    }
};
