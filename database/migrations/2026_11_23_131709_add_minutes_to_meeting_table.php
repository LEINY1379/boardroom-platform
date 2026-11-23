<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->longText('agenda')->nullable()->after('title');
            $table->longText('minutes')->nullable()->after('agenda');
            $table->text('action_items')->nullable()->after('minutes');
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['agenda', 'minutes', 'action_items']);
        });
    }
};