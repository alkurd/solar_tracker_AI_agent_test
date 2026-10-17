<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracker_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('datum_tijd')->useCurrent();
            $table->float('positie_actuator_1');
            $table->float('positie_actuator_2');
            $table->float('opbrengst');
            $table->string('status');
            $table->float('verbruik_actuator_1');
            $table->float('verbruik_actuator_2');
            $table->float('motor_verbruik');
            $table->float('netto_opbrengst');
            $table->float('windsnelheid');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracker_logs');
    }
};