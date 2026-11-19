<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model RadiationLog – Data Sensor Radiasi ESP32
 *
 * @property int         $id
 * @property int         $cpm
 * @property float       $radiation
 * @property string      $status
 * @property int|null    $raw
 * @property string|null $gas
 * @property float|null  $temperature
 * @property float|null  $humidity
 * @property string|null $device_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class RadiationLog extends Model
{
    use HasFactory;

    protected $table = 'radiation_logs';

    protected $fillable = [
        'status',
        'gas',
        'raw',
        'radiation',
        'cpm',
        'temperature',
        'humidity',
        'device_id',
    ];

    protected $casts = [
        'raw' => 'integer',
        'radiation' => 'float',
        'cpm' => 'integer',
        'temperature' => 'float',
        'humidity' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Tentukan status radiasi berdasarkan nilai CPM (Counts Per Minute)
     * Standard ambang batas radiasi latar Ruang Tunggu Radiologi:
     * - AMAN:      < 50 CPM (< 0.25 µSv/h)
     * - WASPADA:   50 - 150 CPM (0.25 - 1.00 µSv/h)
     * - BERBAHAYA: > 150 CPM (> 1.00 µSv/h)
     */
    public static function determineStatus(int $cpm): string
    {
        if ($cpm > 150) {
            return 'BERBAHAYA';
        } elseif ($cpm >= 50) {
            return 'WASPADA';
        } else {
            return 'AMAN';
        }
    }
}
