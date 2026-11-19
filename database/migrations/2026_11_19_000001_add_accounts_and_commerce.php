<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 10)->default('user')->index();
            $table->boolean('is_active')->default(true);
            $table->string('phone', 30)->nullable();
            $table->unsignedBigInteger('store_credit')->default(0);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 50);
            $table->string('recipient', 120);
            $table->string('phone', 30);
            $table->string('street', 500);
            $table->string('city', 120);
            $table->string('province', 120);
            $table->string('postal_code', 12);
            $table->timestamps();
        });
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->string('name', 100);
            $table->string('sku', 100)->unique();
            $table->unsignedBigInteger('price');
            $table->unsignedInteger('stock')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('product_variant_id')->nullable()->constrained();
            $table->string('line_key', 60);
            $table->unsignedInteger('quantity');
            $table->unique(['user_id', 'line_key']);
            $table->timestamps();
        });
        Schema::create('crates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 160)->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#701f38');
            $table->string('cover_image')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('crate_product', function (Blueprint $table) {
            $table->foreignId('crate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['crate_id', 'product_id']);
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('number', 40)->unique();
            $table->uuid('checkout_token')->unique();
            $table->string('fulfillment', 20);
            $table->string('status', 30)->default('pending')->index();
            $table->string('payment_status', 20)->default('pending')->index();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('delivery_fee')->default(0);
            $table->unsignedBigInteger('credit_applied')->default(0);
            $table->unsignedBigInteger('total');
            $table->json('address_snapshot')->nullable();
            $table->text('pickup_instructions')->nullable();
            $table->string('carrier', 120)->nullable();
            $table->string('tracking_number', 120)->nullable();
            $table->string('tracking_url', 1000)->nullable();
            $table->timestamp('inventory_restored_at')->nullable();
            $table->timestamps();
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('product_variant_id')->nullable()->constrained();
            $table->string('name');
            $table->string('variant_name', 100)->nullable();
            $table->unsignedBigInteger('unit_price');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('total');
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained();
            $table->unsignedBigInteger('amount');
            $table->string('method', 30)->default('manual');
            $table->string('status', 20)->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
        Schema::create('order_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('actor_id')->nullable()->constrained('users');
            $table->string('status', 30);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
        Schema::create('trade_in_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('artist', 120);
            $table->string('album', 160);
            $table->string('record_grade', 5);
            $table->string('sleeve_grade', 5);
            $table->text('notes')->nullable();
            $table->json('photos')->nullable();
            $table->string('preference', 20);
            $table->string('status', 30)->default('pending_review')->index();
            $table->unsignedBigInteger('cash_offer')->nullable();
            $table->unsignedBigInteger('credit_offer')->nullable();
            $table->text('offer_notes')->nullable();
            $table->string('accepted_type', 20)->nullable();
            $table->unsignedBigInteger('accepted_amount')->nullable();
            $table->string('delivery_method', 20)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('quote_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_in_quote_id')->constrained();
            $table->foreignId('actor_id')->nullable()->constrained('users');
            $table->string('status', 30);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('actor_id')->nullable()->constrained('users');
            $table->foreignId('order_id')->nullable()->constrained();
            $table->foreignId('trade_in_quote_id')->nullable()->unique()->constrained();
            $table->string('reference', 100)->unique();
            $table->string('type', 20);
            $table->bigInteger('amount');
            $table->unsignedBigInteger('balance_after');
            $table->string('reason', 500);
            $table->timestamp('created_at')->useCurrent();
        });
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users');
            $table->string('action', 60)->index();
            $table->string('subject_type', 60);
            $table->unsignedBigInteger('subject_id');
            $table->text('description');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        foreach (['admin_audit_logs', 'credit_transactions', 'quote_histories', 'trade_in_quotes', 'order_histories', 'payments', 'order_items', 'orders', 'crate_product', 'crates', 'cart_items', 'product_variants', 'addresses'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('products', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['role', 'is_active', 'phone', 'store_credit']));
    }
};
