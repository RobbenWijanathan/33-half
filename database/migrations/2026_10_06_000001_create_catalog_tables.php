<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('product_type')->index(); // vinyl or merchandise
            $table->string('merch_category')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->string('cover_image')->nullable();
            $table->string('audio_preview_url')->nullable();
            $table->unsignedSmallInteger('preview_start_time')->default(0);
            $table->date('release_date')->nullable();
            $table->string('format')->nullable();
            $table->foreignId('label_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_preorder')->default(false);
            $table->timestamps();
        });

        Schema::create('artist_product', function (Blueprint $table) {
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['artist_id', 'product_id']);
        });

        Schema::create('genre_product', function (Blueprint $table) {
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['genre_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genre_product');
        Schema::dropIfExists('artist_product');
        Schema::dropIfExists('products');
        Schema::dropIfExists('labels');
        Schema::dropIfExists('genres');
        Schema::dropIfExists('artists');
    }
};
