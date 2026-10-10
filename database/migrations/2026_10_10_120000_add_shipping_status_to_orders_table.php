<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'shipping_status')) {
                $table->string('shipping_status', 32)->nullable()->after('shipping_address');
            }
            if (! Schema::hasColumn('orders', 'shipping_tracking_code')) {
                $table->string('shipping_tracking_code', 120)->nullable()->after('shipping_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_tracking_code')) {
                $table->dropColumn('shipping_tracking_code');
            }
            if (Schema::hasColumn('orders', 'shipping_status')) {
                $table->dropColumn('shipping_status');
            }
        });
    }
};
