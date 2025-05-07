<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\EncryptionService;

class SecureDataMiddleware
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
        // Daftar field sensitif yang perlu diproses
        $sensitiveFields = [
            'address',
            'city',
            'state',
            'zip_code',
            'email',
            'phone',
            'country',
            'payment_method',
            'telepon'
            // Tambahkan field sensitif lain sesuai kebutuhan
        ];

        // Jika permintaan adalah JSON (seperti dari API)
        if ($request->isJson()) {
            $data = $request->json()->all();
            $this->processInputData($data, $sensitiveFields);
            // Ganti data permintaan dengan data yang telah diproses
            $request->replace($data);
        }
        // Jika form biasa
        else {
            $data = $request->all();
            $this->processInputData($data, $sensitiveFields);
            $request->replace($data);
        }

        // Proses respons untuk dekripsi data jika dibutuhkan
        $response = $next($request);

        // Jika respons berisi data JSON
        if ($response->headers->get('Content-Type') === 'application/json') {
            $responseData = json_decode($response->getContent(), true);

            // Proses data respons jika perlu (misalnya kasus tertentu di mana 
            // data sensitif terenkripsi perlu didekripsi sebelum dikirim ke klien)

            // Kembalikan respons yang diproses
            $response->setContent(json_encode($responseData));
        }

        return $response;
    }

    /**
     * Proses data input secara rekursif
     */
    protected function processInputData(&$data, $sensitiveFields)
    {
        if (!is_array($data)) {
            return;
        }

        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $this->processInputData($value, $sensitiveFields);
            } elseif (isset($sensitiveFields[$key]) && is_string($value)) {
                // Tidak perlu enkripsi lagi jika sudah terenkripsi
                if (!$this->isEncrypted($value)) {
                    $value = $this->encryptionService->encrypt($value, $sensitiveFields[$key]);
                }
            }
        }
    }

    /**
     * Cek apakah data sudah terenkripsi
     */
    protected function isEncrypted($value)
    {
        if (!is_string($value)) {
            return false;
        }

        // Periksa apakah string adalah base64 valid dengan panjang minimal
        return preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $value) &&
            strlen($value) > 40;
    }
}
