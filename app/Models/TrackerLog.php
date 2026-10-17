<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackerLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'datum_tijd',
        'positie_actuator_1',
        'positie_actuator_2',
        'opbrengst',
        'status',
        'modus',
        'systeem_actief',
        'noodstop_actief',
        'verbruik_actuator_1',
        'verbruik_actuator_2',
        'motor_verbruik',
        'netto_opbrengst',
        'windsnelheid',
    ];

    protected function casts(): array
    {
        return [
            'datum_tijd' => 'immutable_datetime',
            'positie_actuator_1' => 'float',
            'positie_actuator_2' => 'float',
            'opbrengst' => 'float',
            'systeem_actief' => 'boolean',
            'noodstop_actief' => 'boolean',
            'verbruik_actuator_1' => 'float',
            'verbruik_actuator_2' => 'float',
            'motor_verbruik' => 'float',
            'netto_opbrengst' => 'float',
            'windsnelheid' => 'float',
        ];
    }
}