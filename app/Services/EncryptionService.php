<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Config;

class EncryptionService
{
    protected $cipher = 'aes-256-cbc';
    protected $keyManager;

    public function __construct(KeyManagementService $keyManager)
    {
        $this->keyManager = $keyManager;
    }

    /**
     * Enkripsi data sensitif
     *
     * @param mixed $data Data yang akan dienkripsi
     * @param string $context Konteks data (misalnya: 'payment', 'personal', 'medical')
     * @return string Data terenkripsi dalam bentuk base64
     */
    public function encrypt($data, $context = 'default')
    {
        try {
            // Ambil kunci enkripsi dari key management service
            $key = $this->keyManager->getKey($context);

            // Generate initialization vector (IV) acak
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));

            // Konversi data menjadi string jika bukan string
            if (!is_string($data)) {
                $data = json_encode($data);
            }

            // Enkripsi data
            $encrypted = openssl_encrypt(
                $data,
                $this->cipher,
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($encrypted === false) {
                throw new Exception('Enkripsi gagal: ' . openssl_error_string());
            }

            // Gabungkan IV dan data terenkripsi dan konversi ke base64
            $result = base64_encode($iv . $encrypted);

            // Log aktivitas enkripsi (tanpa data sensitif)
            Log::info("Data {$context} berhasil dienkripsi", [
                'context' => $context,
                'timestamp' => now(),
                'operation' => 'encrypt'
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Kesalahan enkripsi: ' . $e->getMessage());
            throw new Exception('Gagal mengenkripsi data: ' . $e->getMessage());
        }
    }

    /**
     * Dekripsi data terenkripsi
     *
     * @param string $encryptedData Data terenkripsi dalam bentuk base64
     * @param string $context Konteks data (misalnya: 'payment', 'personal', 'medical')
     * @return mixed Data yang telah didekripsi
     */
    public function decrypt($encryptedData, $context = 'default')
    {
        try {
            // Ambil kunci dekripsi dari key management service
            $key = $this->keyManager->getKey($context);

            // Decode data dari base64
            $decodedData = base64_decode($encryptedData);

            // Ekstrak IV dari data terenkripsi
            $ivSize = openssl_cipher_iv_length($this->cipher);
            $iv = substr($decodedData, 0, $ivSize);
            $encryptedData = substr($decodedData, $ivSize);

            // Dekripsi data
            $decrypted = openssl_decrypt(
                $encryptedData,
                $this->cipher,
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($decrypted === false) {
                throw new Exception('Dekripsi gagal: ' . openssl_error_string());
            }

            // Log aktivitas dekripsi (tanpa data sensitif)
            Log::info("Data {$context} berhasil didekripsi", [
                'context' => $context,
                'timestamp' => now(),
                'operation' => 'decrypt'
            ]);

            // Jika data adalah JSON, decode
            if ($this->isJson($decrypted)) {
                return json_decode($decrypted, true);
            }

            return $decrypted;
        } catch (Exception $e) {
            Log::error('Kesalahan dekripsi: ' . $e->getMessage());
            throw new Exception('Gagal mendekripsi data: ' . $e->getMessage());
        }
    }

    /**
     * Periksa apakah string adalah JSON valid
     */
    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
