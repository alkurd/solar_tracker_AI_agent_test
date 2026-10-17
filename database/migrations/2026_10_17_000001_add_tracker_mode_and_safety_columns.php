<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracker_logs', function (Blueprint $table) {
            $table->string('modus')->nullable();
            $table->boolean('systeem_actief')->nullable();
            $table->boolean('noodstop_actief')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tracker_logs', function (Blueprint $table) {
            $table->dropColumn(['modus', 'systeem_actief', 'noodstop_actief']);
        });
    }
};