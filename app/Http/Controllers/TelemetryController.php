<?php

namespace App\Http\Controllers;

use App\Models\RadiationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * TelemetryController
 *
 * Mengelola data sensor radiasi dari ESP32:
 * - Dashboard utama
 * - API record  : menerima data dari ESP32
 * - API latest  : polling dashboard (data terbaru)
 * - API history : tabel & grafik riwayat
 * - API clear   : reset data (hanya admin)
 *
 * @property-read \App\Models\User $user
 */
class TelemetryController extends Controller
{
    // ----------------------------------------------------------------
    // DASHBOARD
    // ----------------------------------------------------------------

    /**
     * Tampilkan Halaman Dashboard Utama
     */
    public function index(Request $request)
    {
        if ($request->has('clear') || $request->has('reset')) {
            /** @var \App\Models\User $authUser */
            $authUser = Auth::user();
            if (!Auth::check() || !$authUser->isAdmin()) {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak: Hanya Administrator yang berhak mengosongkan data!');
            }
            RadiationLog::truncate();
            return redirect()->route('dashboard')
                ->with('success', 'Data sensor radiasi berhasil dikosongkan. Menunggu data dari ESP32.');
        }

        $latest = RadiationLog::latest('id')->first();
        return view('dashboard', compact('latest'));
    }

    // ----------------------------------------------------------------
    // API – RESET DATA
    // ----------------------------------------------------------------

    /**
     * Kosongkan seluruh data sensor (Hanya Admin)
     */
    public function clear(Request $request)
    {
        /** @var \App\Models\User|null $authUser */
        $authUser = Auth::user();

        if (!Auth::check() || !$authUser->isAdmin()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: Hanya Administrator yang berhak mengosongkan seluruh data!',
                ], 403);
            }
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak: Hanya Administrator yang berhak mengosongkan data!');
        }

        RadiationLog::truncate();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Semua data sensor berhasil dikosongkan.',
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Semua data sensor berhasil dikosongkan!');
    }

    // ----------------------------------------------------------------
    // API – TERIMA DATA DARI ESP32
    // ----------------------------------------------------------------

    /**
     * Simpan data radiasi yang dikirim ESP32
     *
     * Format JSON yang diterima:
     *   { "cpm": 28, "radiation": 0.18, "temp": 24.5, "hum": 56.0 }
     * Alias yang didukung: raw, counts, usvh, temperature, humidity, suhu, kelembapan
     */
    public function record(Request $request)
    {
        // 1. Ambil CPM (alias: cpm / counts / raw)
        $cpmInput = $request->input('cpm',
                    $request->input('counts',
                    $request->input('raw')));

        // 2. Ambil radiasi µSv/h jika dikirim langsung (alias: radiation / usvh)
        $radInput = $request->input('radiation',
                    $request->input('usvh'));

        if ($cpmInput !== null) {
            $cpm = (int) $cpmInput;
            // Nilai ADC 0-4095 → petakan ke 0-250 CPM
            if ($cpm > 400 && $radInput === null) {
                $cpm = (int) round(($cpm / 4095) * 250);
            }
            $radiation = $radInput !== null ? (float) $radInput : round($cpm / 153.8, 3);
        } elseif ($radInput !== null) {
            $radiation = (float) $radInput;
            $cpm       = (int) round($radiation * 153.8);
        } else {
            $cpm       = 0;
            $radiation = 0.0;
        }

        // 3. Status radiasi
        $statusInput = $request->input('status');
        if (!empty($statusInput)) {
            $up = strtoupper(trim((string) $statusInput));
            if (in_array($up, ['SEDANG', 'WASPADA', 'WARNING'], true)) {
                $status = 'WASPADA';
            } elseif (in_array($up, ['BERBAHAYA', 'BAHAYA', 'DANGER'], true)) {
                $status = 'BERBAHAYA';
            } else {
                $status = 'AMAN';
            }
        } else {
            $status = RadiationLog::determineStatus($cpm);
        }

        // 4. Lingkungan (DHT22)
        $temperature = $request->input('temp',
                       $request->input('temperature',
                       $request->input('suhu')));

        $humidity    = $request->input('hum',
                       $request->input('humidity',
                       $request->input('kelembapan')));

        $deviceId    = $request->input('device_id',
                       $request->input('device', 'ESP32-Geiger01'));

        // 5. Simpan ke MySQL
        $log = RadiationLog::create([
            'cpm'         => $cpm,
            'radiation'   => $radiation,
            'status'      => $status,
            'raw'         => $cpm,
            'gas'         => $status,
            'temperature' => $temperature !== null ? (float) $temperature : null,
            'humidity'    => $humidity    !== null ? (float) $humidity    : null,
            'device_id'   => $deviceId,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data monitoring radiasi dari ESP32 berhasil disimpan ke database.',
            'data'    => [
                'id'          => $log->id,
                'cpm'         => $log->cpm,
                'radiation'   => $log->radiation,
                'status'      => $log->status,
                'temperature' => $log->temperature,
                'humidity'    => $log->humidity,
                'device_id'   => $log->device_id,
                'created_at'  => $log->created_at->format('Y-m-d H:i:s'),
                'time'        => $log->created_at->format('H:i:s'),
                'date'        => $log->created_at->translatedFormat('d M Y'),
            ],
        ], 201);
    }

    // ----------------------------------------------------------------
    // API – DATA TERBARU (POLLING DASHBOARD)
    // ----------------------------------------------------------------

    /**
     * Kembalikan data sensor terbaru untuk polling dashboard
     */
    public function latest()
    {
        $now = Carbon::now();

        $serverTime = [
            'date'    => $now->translatedFormat('l, d F Y'),
            'time'    => $now->format('H:i:s'),
            'hours'   => $now->format('H'),
            'minutes' => $now->format('i'),
            'seconds' => $now->format('s'),
        ];

        /** @var \App\Models\RadiationLog|null $log */
        $log = RadiationLog::latest('id')->first();

        if (!$log) {
            return response()->json([
                'status'      => 'empty',
                'has_data'    => false,
                'message'     => 'Belum ada data dari ESP32.',
                'server_time' => $serverTime,
            ]);
        }

        // ESP32 kirim setiap 30 detik → online jika < 90 detik yang lalu
        $diffSeconds = (int) abs($now->diffInSeconds($log->created_at));
        $isOnline    = $diffSeconds <= 90;

        $cpm       = (int)   ($log->cpm       ?? $log->raw ?? 0);
        $radiation = (float) ($log->radiation ?? round($cpm / 153.8, 3));

        return response()->json([
            'status'                => 'success',
            'has_data'              => true,
            'is_online'             => $isOnline,
            'last_seen_seconds_ago' => $diffSeconds,
            'server_time'           => $serverTime,
            'data'                  => [
                'id'          => $log->id,
                'cpm'         => $cpm,
                'radiation'   => $radiation,
                'status'      => $log->status,
                'temperature' => $log->temperature !== null ? (float) $log->temperature : null,
                'humidity'    => $log->humidity    !== null ? (float) $log->humidity    : null,
                'device_id'   => $log->device_id,
                'timestamp'   => $log->created_at->format('Y-m-d H:i:s'),
                'time'        => $log->created_at->format('H:i:s'),
                'date'        => $log->created_at->translatedFormat('d M Y'),
            ],
        ]);
    }

    // ----------------------------------------------------------------
    // API – RIWAYAT DATA (TABEL & GRAFIK)
    // ----------------------------------------------------------------

    /**
     * Kembalikan riwayat data sensor untuk tabel & grafik
     */
    public function history(Request $request)
    {
        $limit = min(100, max(10, (int) $request->input('limit', 30)));

        $logs = RadiationLog::latest('id')
            ->take($limit)
            ->get()
            ->reverse()
            ->values();

        $formatted = $logs->map(function (RadiationLog $item) {
            $cpm       = (int)   ($item->cpm       ?? $item->raw ?? 0);
            $radiation = (float) ($item->radiation ?? round($cpm / 153.8, 3));

            return [
                'id'          => $item->id,
                'cpm'         => $cpm,
                'radiation'   => $radiation,
                'status'      => $item->status,
                'temperature' => $item->temperature !== null ? (float) $item->temperature : null,
                'humidity'    => $item->humidity    !== null ? (float) $item->humidity    : null,
                'device_id'   => $item->device_id,
                'time'        => $item->created_at->format('H:i:s'),
                'date'        => $item->created_at->translatedFormat('d M Y'),
                'timestamp'   => $item->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'count'  => $logs->count(),
            'data'   => $formatted,
        ]);
    }
}
