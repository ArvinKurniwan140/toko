<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Services\SecurityLogger;
use Exception;

class SecurityRateLimiter
{
    /**
     * Batas operasi per menit untuk berbagai aksi
     *
     * @var array
     */
    protected static $limits = [
        'encrypt' => 100,     // 100 operasi enkripsi per menit
        'decrypt' => 100,     // 100 operasi dekripsi per menit
        'key_fetch' => 200,   // 200 operasi pengambilan kunci per menit
        'key_rotation' => 5,  // 5 operasi rotasi kunci per menit
    ];

    /**
     * Periode blokir setelah mencapai batas (dalam menit)
     *
     * @var int
     */
    protected static $blockDuration = 5;

    /**
     * Periksa dan terapkan pembatasan laju untuk operasi keamanan
     *
     * @param string $operation Jenis operasi
     * @param string $identifier Pengidentifikasi unik (default: user_id atau IP)
     * @return bool True jika diizinkan, False jika melebihi batas
     * @throws Exception Jika operasi diblokir
     */
    public static function check($operation, $identifier = null)
    {
        // Gunakan user_id jika tersedia, kalau tidak gunakan IP
        if ($identifier === null) {
            $identifier = Auth::id() ?? Request::ip();
        }

        // Batas operasi default jika tidak ada dalam daftar
        $limit = self::$limits[$operation] ?? 60;

        $cacheKey = "rate_limit:{$operation}:{$identifier}";
        $blockKey = "rate_limit_blocked:{$operation}:{$identifier}";

        // Periksa apakah pengguna/IP sedang diblokir
        if (Cache::has($blockKey)) {
            $remainingTime = Cache::get($blockKey);

            SecurityLogger::log(
                'rate_limit_exceeded',
                $operation,
                'failure',
                [
                    'identifier' => $identifier,
                    'blocked_until' => $remainingTime,
                    'limit' => $limit
                ]
            );

            throw new Exception("Operasi {$operation} dibatasi. Coba lagi setelah {$remainingTime} detik.");
        }

        // Dapatkan jumlah operasi yang sudah dilakukan
        $currentCount = Cache::get($cacheKey, 0);

        // Jika pertama kali, set TTL 1 menit
        if ($currentCount === 0) {
            Cache::put($cacheKey, 1, 60);
            return true;
        }

        // Jika sudah mencapai batas
        if ($currentCount >= $limit) {
            // Blokir untuk periode tertentu
            $blockUntil = now()->addMinutes(self::$blockDuration);
            Cache::put($blockKey, $blockUntil->diffInSeconds(now()), self::$blockDuration * 60);

            SecurityLogger::log(
                'rate_limit_exceeded',
                $operation,
                'failure',
                [
                    'identifier' => $identifier,
                    'count' => $currentCount,
                    'limit' => $limit,
                    'block_duration' => self::$blockDuration
                ]
            );

            throw new Exception("Batas operasi {$operation} terlampaui. Coba lagi setelah " . self::$blockDuration . " menit.");
        }

        // Increment count
        Cache::increment($cacheKey);

        return true;
    }

    /**
     * Setel batas kustom untuk operasi tertentu
     *
     * @param string $operation Jenis operasi
     * @param int $limit Batas operasi per menit
     */
    public static function setLimit($operation, $limit)
    {
        self::$limits[$operation] = $limit;
    }

    /**
     * Setel durasi blokir default
     *
     * @param int $minutes Durasi blokir dalam menit
     */
    public static function setBlockDuration($minutes)
    {
        self::$blockDuration = $minutes;
    }

    /**
     * Reset penghitung untuk operasi tertentu
     *
     * @param string $operation Jenis operasi
     * @param string $identifier Pengidentifikasi (user_id atau IP)
     */
    public static function reset($operation, $identifier = null)
    {
        if ($identifier === null) {
            $identifier = Auth::id() ?? Request::ip();
        }

        $cacheKey = "rate_limit:{$operation}:{$identifier}";
        $blockKey = "rate_limit_blocked:{$operation}:{$identifier}";

        Cache::forget($cacheKey);
        Cache::forget($blockKey);
    }
}
