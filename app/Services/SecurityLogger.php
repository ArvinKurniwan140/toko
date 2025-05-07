<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class SecurityLogger
{
    /**
     * Log aktivitas keamanan dengan detail lengkap
     *
     * @param string $operation Jenis operasi (encrypt, decrypt, key_rotation, dll)
     * @param string $context Konteks data (payment, personal, medical, dll)
     * @param string $status Status operasi (success, failure)
     * @param array $additionalData Data tambahan yang perlu dicatat
     * @param \Exception|null $exception Exception jika ada
     */
    public static function log($operation, $context, $status, array $additionalData = [], \Exception $exception = null)
    {
        $userId = Auth::id() ?? 'unauthenticated';
        $username = Auth::user()->username ?? 'unknown';

        $logData = [
            'user_id' => $userId,
            'username' => $username,
            'operation' => $operation,
            'context' => $context,
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'request_id' => request()->header('X-Request-ID') ?? uniqid('req-'),
        ];

        // Tambahkan data tambahan ke log
        $logData = array_merge($logData, $additionalData);

        // Tambahkan informasi exception jika ada
        if ($exception) {
            $logData['error'] = [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        // Log dengan level yang sesuai berdasarkan status
        if ($status === 'success') {
            Log::info('Security Event: ' . $operation, $logData);
        } else {
            Log::error('Security Event: ' . $operation, $logData);
        }

        // Untuk operasi sangat sensitif, log ke channel terpisah jika dikonfigurasi
        if (in_array($operation, ['key_rotation', 'access_denied', 'brute_force_detected'])) {
            Log::channel('security')->critical('Critical Security Event: ' . $operation, $logData);
        }
    }

    /**
     * Log percobaan akses yang tidak sah
     *
     * @param string $operation Jenis operasi yang dicoba
     * @param string $context Konteks data
     * @param string $reason Alasan penolakan
     */
    public static function logAccessDenied($operation, $context, $reason)
    {
        self::log(
            'access_denied',
            $context,
            'failure',
            [
                'attempted_operation' => $operation,
                'reason' => $reason
            ]
        );
    }

    /**
     * Log aktivitas yang berkaitan dengan kunci
     *
     * @param string $operation Operasi kunci (rotation, creation, fetching)
     * @param string $context Konteks kunci
     * @param string $status Status operasi
     * @param array $additionalData Data tambahan
     */
    public static function logKeyOperation($operation, $context, $status, array $additionalData = [])
    {
        $keyOperationData = [
            'key_operation' => $operation,
        ];

        self::log(
            'key_management',
            $context,
            $status,
            array_merge($keyOperationData, $additionalData)
        );
    }
}
