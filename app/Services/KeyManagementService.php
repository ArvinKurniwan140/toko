<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Exception;

class KeyManagementService
{
    /**
     * Waktu cache kunci dalam menit
     */
    protected $cacheTtl = 30;

    /**
     * Path untuk menyimpan kunci terenkripsi
     */
    protected $keyStoragePath = null;

    /**
     * Inisialisasi service
     */
    public function __construct()
    {
        $this->keyStoragePath = storage_path('keys');


        if (!File::exists($this->keyStoragePath)) {
            File::makeDirectory($this->keyStoragePath, 0700, true);
        }
    }

    /**
     * Mendapatkan kunci enkripsi berdasarkan konteks
     * 
     * @param string $context Konteks penggunaan kunci (misalnya: 'payment', 'personal', 'medical')
     * @return string Kunci enkripsi
     */
    public function getKey($context = 'default')
    {
        // Cache key untuk mengurangi permintaan ke KMS
        $cacheKey = "encryption_key_{$context}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Dalam produksi, ini akan memanggil layanan KMS eksternal
        // seperti AWS KMS, Azure Key Vault, atau Google Cloud KMS
        $key = $this->retrieveKeyFromKMS($context);

        // Cache kunci untuk beberapa waktu
        Cache::put($cacheKey, $key, $this->cacheTtl * 60);

        return $key;
    }

    /**
     * Rotasi kunci enkripsi
     * 
     * @param string $context Konteks kunci yang akan dirotasi
     * @return bool Status rotasi kunci
     */
    public function rotateKey($context = 'default')
    {
        try {
            // Mendapatkan kunci lama
            $oldKey = $this->getKey($context);

            // Tanggal rotasi sebagai versi
            $version = date('YmdHis');

            // Arsipkan kunci lama dengan versi
            $this->archiveOldKey($context, $oldKey, $version);

            // Generate kunci baru
            $newKey = $this->generateNewKey();

            // Simpan kunci baru di KMS
            $this->storeKeyInKMS($context, $newKey);

            // Hapus cache kunci lama
            Cache::forget("encryption_key_{$context}");

            // Log aktivitas rotasi
            Log::info("Rotasi kunci untuk konteks '{$context}' berhasil dengan versi {$version}");

            return true;
        } catch (Exception $e) {
            Log::error("Rotasi kunci gagal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mendapatkan kunci lama berdasarkan versi
     * Berguna untuk mendekripsi data lama setelah rotasi kunci
     * 
     * @param string $context Konteks kunci
     * @param string $version Versi kunci
     * @return string Kunci enkripsi versi lama
     */
    public function getKeyByVersion($context, $version)
    {
        // Cache key untuk mengurangi permintaan ke KMS
        $cacheKey = "encryption_key_{$context}_v{$version}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Dalam produksi, ini akan memanggil KMS untuk mendapatkan kunci versi lama
        $key = $this->retrieveArchivedKeyFromKMS($context, $version);

        // Cache kunci untuk beberapa waktu
        Cache::put($cacheKey, $key, $this->cacheTtl * 60);

        return $key;
    }

    /**
     * Enkripsi ulang data setelah rotasi kunci
     * 
     * @param string $context Konteks kunci
     * @param string $oldVersion Versi kunci lama
     * @param callable $dataProcessor Fungsi untuk memproses data
     * @return bool Status re-enkripsi
     */
    public function reencryptData($context, $oldVersion, callable $dataProcessor)
    {
        try {
            // Dapatkan kunci lama
            $oldKey = $this->getKeyByVersion($context, $oldVersion);

            // Dapatkan kunci baru
            $newKey = $this->getKey($context);

            // Callback untuk memproses data
            $dataProcessor($oldKey, $newKey);

            return true;
        } catch (Exception $e) {
            Log::error("Re-enkripsi data gagal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mendapatkan kunci dari KMS eksternal atau file lokal
     * 
     * CATATAN: Dalam produksi, metode ini akan memanggil 
     * API KMS seperti AWS KMS, Azure Key Vault, atau Google Cloud KMS
     */
    private function retrieveKeyFromKMS($context)
    {
        // Cek apakah ada kunci di environment
        $envKey = env('ENCRYPTION_KEY_' . strtoupper($context));
        if ($envKey) {
            return base64_decode($envKey);
        }

        // Cek apakah ada file kunci lokal
        $keyFilePath = $this->keyStoragePath . "/aes_key_{$context}.key";
        if (File::exists($keyFilePath)) {
            return base64_decode(File::get($keyFilePath));
        }

        // Jika tidak ada kunci spesifik, gunakan kunci default
        $defaultEnvKey = env('ENCRYPTION_KEY');
        if ($defaultEnvKey) {
            return base64_decode($defaultEnvKey);
        }

        // Jika tidak ada kunci default di env, gunakan app key sebagai basis
        // CATATAN: Ini bukan praktik terbaik untuk produksi!
        $baseKey = Config::get('app.key');
        if (empty($baseKey)) {
            throw new Exception('Tidak ada kunci enkripsi yang ditemukan dan app.key kosong');
        }

        // Derivasi kunci berbeda untuk setiap konteks
        return hash_hmac('sha256', $context, base64_decode(substr($baseKey, 7)), true);
    }

    /**
     * Menghasilkan kunci baru untuk AES-256
     */
    private function generateNewKey()
    {
        return random_bytes(32); // 256 bit key
    }

    /**
     * Menyimpan kunci di KMS
     * 
     * CATATAN: Implementasi simulasi! Dalam produksi akan menggunakan KMS sesungguhnya
     */
    private function storeKeyInKMS($context, $key)
    {
        // Jika KMS external belum diimplementasikan, simpan ke file lokal
        $keyFilePath = $this->keyStoragePath . "/aes_key_{$context}.key";
        File::put($keyFilePath, base64_encode($key));
        File::chmod($keyFilePath, 0600); // Restrict access

        // Log aktivitas penyimpanan kunci
        Log::info("Kunci baru untuk konteks '{$context}' disimpan");

        return true;
    }

    /**
     * Mengarsipkan kunci lama dengan versi
     */
    private function archiveOldKey($context, $oldKey, $version)
    {
        // Simpan kunci lama dengan versi
        $archiveFilePath = $this->keyStoragePath . "/aes_key_{$context}_v{$version}.key";
        File::put($archiveFilePath, base64_encode($oldKey));
        File::chmod($archiveFilePath, 0600); // Restrict access

        // Log aktivitas pengarsipan
        Log::info("Kunci lama untuk konteks '{$context}' diarsipkan dengan versi {$version}");

        return $version;
    }

    /**
     * Mengambil kunci yang diarsipkan dari KMS
     */
    private function retrieveArchivedKeyFromKMS($context, $version)
    {
        // Cek apakah ada file kunci arsip lokal
        $archiveFilePath = $this->keyStoragePath . "/aes_key_{$context}_v{$version}.key";
        if (File::exists($archiveFilePath)) {
            return base64_decode(File::get($archiveFilePath));
        }

        // Jika tidak ada file arsip, dan ini adalah simulasi, gunakan app key sebagai basis
        // CATATAN: Ini bukan praktik terbaik untuk produksi!
        $baseKey = Config::get('app.key');

        // Derivasi kunci berbeda untuk setiap konteks dan versi
        return hash_hmac('sha256', "{$context}_{$version}", base64_decode(substr($baseKey, 7)), true);
    }

    /**
     * Dapatkan semua versi kunci yang tersedia untuk konteks tertentu
     */
    public function getAvailableKeyVersions($context)
    {
        $versions = [];
        $pattern = $this->keyStoragePath . "/aes_key_{$context}_v*.key";

        foreach (glob($pattern) as $file) {
            $filename = basename($file);
            preg_match("/aes_key_{$context}_v(.*).key/", $filename, $matches);
            if (isset($matches[1])) {
                $versions[] = $matches[1];
            }
        }

        return $versions;
    }
}
