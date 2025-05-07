<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class HybridEncryptionService
{
    protected $encryptionService;
    protected $rsaKeyPath;
    protected $cipher = 'aes-256-cbc';

    /**
     * Buat instance baru dari layanan enkripsi hybrid
     */
    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
        $this->rsaKeyPath = storage_path('app/keys');

        // Buat direktori kunci jika belum ada
        if (!file_exists($this->rsaKeyPath)) {
            mkdir($this->rsaKeyPath, 0700, true);
        }
    }

    /**
     * Generate pasangan kunci RSA
     */
    public function generateRSAKeyPair($userId)
    {
        $config = [
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        // Buat pasangan kunci
        $keyPair = openssl_pkey_new($config);

        // Ekstrak kunci privat
        openssl_pkey_export($keyPair, $privateKey);

        // Ekstrak kunci publik
        $publicKey = openssl_pkey_get_details($keyPair)["key"];

        // Simpan kunci ke file
        file_put_contents("{$this->rsaKeyPath}/private_key_{$userId}.pem", $privateKey);
        file_put_contents("{$this->rsaKeyPath}/public_key_{$userId}.pem", $publicKey);

        chmod("{$this->rsaKeyPath}/private_key_{$userId}.pem", 0600);
        chmod("{$this->rsaKeyPath}/public_key_{$userId}.pem", 0644);

        return [
            'private' => $privateKey,
            'public' => $publicKey
        ];
    }

    /**
     * Enkripsi data menggunakan hybrid AES-256 + RSA
     *
     * AES-256 digunakan untuk mengenkripsi data utama karena lebih efisien
     * RSA digunakan untuk mengenkripsi kunci AES
     */
    public function encrypt($data, $userId, $context = 'default')
    {
        try {
            // 1. Generate kunci AES acak untuk data ini
            $aesKey = openssl_random_pseudo_bytes(32); // 256 bit
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));

            // 2. Enkripsi data menggunakan AES
            $encryptedData = openssl_encrypt(
                is_string($data) ? $data : json_encode($data),
                $this->cipher,
                $aesKey,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($encryptedData === false) {
                throw new Exception('Enkripsi AES gagal: ' . openssl_error_string());
            }

            // 3. Ambil kunci publik RSA user
            $publicKeyPath = "{$this->rsaKeyPath}/public_key_{$userId}.pem";

            // Buat kunci RSA jika belum ada
            if (!file_exists($publicKeyPath)) {
                $this->generateRSAKeyPair($userId);
            }

            $publicKey = file_get_contents($publicKeyPath);

            // 4. Enkripsi kunci AES dengan RSA
            $encryptedAesKey = '';
            $encryptResult = openssl_public_encrypt($aesKey, $encryptedAesKey, $publicKey);

            if (!$encryptResult) {
                throw new Exception('Enkripsi RSA gagal: ' . openssl_error_string());
            }

            // 5. Buat paket data terenkripsi dalam format:
            // [Kunci AES terenkripsi dengan RSA]:[IV]:[Data terenkripsi dengan AES]
            $result = base64_encode($encryptedAesKey) . ':' .
                base64_encode($iv) . ':' .
                base64_encode($encryptedData);

            // Log aktivitas enkripsi
            Log::info("Data {$context} berhasil dienkripsi dengan hybrid AES+RSA", [
                'context' => $context,
                'user_id' => $userId,
                'timestamp' => now(),
                'operation' => 'hybrid_encrypt'
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Kesalahan enkripsi hybrid: ' . $e->getMessage());
            throw new Exception('Gagal mengenkripsi data: ' . $e->getMessage());
        }
    }

    /**
     * Dekripsi data yang dienkripsi dengan hybrid AES-256 + RSA
     */
    public function decrypt($encryptedData, $userId, $context = 'default')
    {
        try {
            // 1. Parse komponen-komponen data terenkripsi
            list($encryptedAesKey, $iv, $encryptedContent) = explode(':', $encryptedData);

            $encryptedAesKey = base64_decode($encryptedAesKey);
            $iv = base64_decode($iv);
            $encryptedContent = base64_decode($encryptedContent);

            // 2. Ambil kunci privat RSA user
            $privateKeyPath = "{$this->rsaKeyPath}/private_key_{$userId}.pem";

            if (!file_exists($privateKeyPath)) {
                throw new Exception('Kunci privat RSA tidak ditemukan untuk user ini');
            }

            $privateKey = file_get_contents($privateKeyPath);

            // 3. Dekripsi kunci AES menggunakan RSA
            $aesKey = '';
            $decryptResult = openssl_private_decrypt($encryptedAesKey, $aesKey, $privateKey);

            if (!$decryptResult) {
                throw new Exception('Dekripsi kunci AES gagal: ' . openssl_error_string());
            }

            // 4. Dekripsi data menggunakan AES
            $decrypted = openssl_decrypt(
                $encryptedContent,
                $this->cipher,
                $aesKey,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($decrypted === false) {
                throw new Exception('Dekripsi data AES gagal: ' . openssl_error_string());
            }

            // 5. Log aktivitas dekripsi
            Log::info("Data {$context} berhasil didekripsi dengan hybrid AES+RSA", [
                'context' => $context,
                'user_id' => $userId,
                'timestamp' => now(),
                'operation' => 'hybrid_decrypt'
            ]);

            // 6. Kembalikan data asli
            if ($this->isJson($decrypted)) {
                return json_decode($decrypted, true);
            }

            return $decrypted;
        } catch (Exception $e) {
            Log::error('Kesalahan dekripsi hybrid: ' . $e->getMessage());
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
