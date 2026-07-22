<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracking_links', function (Blueprint $table) {

            // Status for filtering
            $table->enum('status', [
                'active',
                'deleted'
            ])
                ->default('active')
                ->after('click_count');


            // Soft delete column
            $table->softDeletes();
        });
    }


    public function down(): void
    {
        Schema::table('tracking_links', function (Blueprint $table) {

            $table->dropColumn('status');

            $table->dropSoftDeletes();
        });
    }
};
