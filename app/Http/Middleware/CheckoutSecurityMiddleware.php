<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\EncryptionService;
use Symfony\Component\HttpFoundation\Response;

class CheckoutSecurityMiddleware
{
    protected $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */


    public function handle(Request $request, Closure $next): Response
    {
        // 1. Rate limiting untuk mencegah brute force
        $ip = $request->ip();
        $cacheKey = 'checkout_attempts_' . $ip;

        $attempts = Cache::get($cacheKey, 0);

        if ($attempts > 10) {
            Log::warning("Terlalu banyak percobaan checkout dari IP: {$ip}");
            return response()->json(['error' => 'Terlalu banyak percobaan. Coba lagi nanti.'], 429);
        }

        Cache::put($cacheKey, $attempts + 1, 300); // 5 menit

        // 2. Verifikasi token CSRF
        if ($request->isMethod('POST') && !$request->hasValidSignature()) {
            Log::warning("Percobaan checkout tanpa token valid: {$ip}");
            return response()->json(['error' => 'Akses ditolak. Token tidak valid.'], 403);
        }

        // 3. Validasi HTTP header untuk mencegah MITM
        if (!$request->secure() && app()->environment('production')) {
            Log::warning("Percobaan checkout melalui koneksi tidak aman: {$ip}");
            return response()->json(['error' => 'Checkout hanya dapat dilakukan melalui koneksi aman (HTTPS).'], 403);
        }

        // 4. Anti-Automation - Deteksi bot sederhana
        $userAgent = $request->header('User-Agent');
        $acceptHeader = $request->header('Accept');

        if (empty($userAgent) || empty($acceptHeader)) {
            Log::warning("Percobaan checkout dengan header tidak lengkap: {$ip}");
            return response()->json(['error' => 'Request tidak valid.'], 400);
        }

        // 5. Verifikasi session untuk mencegah session hijacking
        if (!$request->hasSession() || !$request->session()->has('_token')) {
            Log::warning("Percobaan checkout tanpa session valid: {$ip}");
            return response()->json(['error' => 'Session tidak valid.'], 403);
        }

        // Tambahkan HSTS header di produksi
        if (app()->environment('production')) {
            $response = $next($request);
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            return $response;
        }

        return $next($request);
    }
}
