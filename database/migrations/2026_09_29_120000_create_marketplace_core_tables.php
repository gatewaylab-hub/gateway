<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('marketplace_category_id')->nullable()->after('category')->constrained('marketplace_categories')->nullOnDelete();
            $table->string('delivery_mode', 32)->default('chat')->after('type');
            $table->string('warranty_text')->nullable()->after('delivery_mode');
            $table->string('region_text')->nullable()->after('warranty_text');
            $table->json('marketplace_meta')->nullable()->after('region_text');
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('updated_at');
            }
        });

        Schema::create('product_questions', function (Blueprint $table) {
            $table->id();
            $table->char('product_id', 36);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index(['product_id', 'created_at']);
            $table->index(['tenant_id', 'answered_at']);
        });

        Schema::create('product_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_question_id')->constrained('product_questions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('order_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->char('product_id', 36)->nullable();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->index(['seller_id', 'last_message_at']);
            $table->index(['buyer_id', 'last_message_at']);
        });

        Schema::create('order_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_conversation_id')->constrained('order_conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 32)->default('user'); // user|system|delivery
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['order_conversation_id', 'created_at']);
        });

        Schema::create('product_delivery_codes', function (Blueprint $table) {
            $table->id();
            $table->char('product_id', 36);
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->text('payload_encrypted');
            $table->string('status', 32)->default('available'); // available|sold|reserved
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index(['product_id', 'status']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_delivery_codes');
        Schema::dropIfExists('order_messages');
        Schema::dropIfExists('order_conversations');
        Schema::dropIfExists('product_answers');
        Schema::dropIfExists('product_questions');

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('marketplace_category_id');
            $table->dropColumn(['delivery_mode', 'warranty_text', 'region_text', 'marketplace_meta']);
        });

        if (Schema::hasColumn('users', 'last_seen_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('last_seen_at');
            });
        }

        Schema::dropIfExists('marketplace_categories');
    }
};
