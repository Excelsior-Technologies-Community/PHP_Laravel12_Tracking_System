<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tracking_links', function (Blueprint $table) {
            $table->string('password')->nullable()->after('status');
            $table->dateTime('expires_at')->nullable()->after('password');
            $table->unsignedInteger('click_limit')->nullable()->after('expires_at');
            $table->string('qr_color')->default('#000000')->after('click_limit');
            $table->string('qr_bg_color')->default('#ffffff')->after('qr_color');
            $table->string('utm_source')->nullable()->after('qr_bg_color');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
            $table->string('utm_term')->nullable()->after('utm_campaign');
        });

        Schema::table('clicks', function (Blueprint $table) {
            $table->boolean('is_bot')->default(false)->after('platform');
            $table->boolean('is_unique')->default(true)->after('is_bot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking_links', function (Blueprint $table) {
            $table->dropColumn([
                'password',
                'expires_at',
                'click_limit',
                'qr_color',
                'qr_bg_color',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_term',
            ]);
        });

        Schema::table('clicks', function (Blueprint $table) {
            $table->dropColumn(['is_bot', 'is_unique']);
        });
    }
};
