<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use App\Services\EncryptionService;

trait EncryptsAttributes
{
    /**
     * Boot the trait
     */
    public static function bootEncryptsAttributes()
    {
        static::saving(function ($model) {
            $model->encryptAttributes();
        });

        static::retrieved(function ($model) {
            $model->decryptAttributes();
        });
    }

    /**
     * Enkripsi atribut-atribut yang ditentukan
     */
    protected function encryptAttributes()
    {
        if (empty($this->encrypts) || !is_array($this->encrypts)) {
            return;
        }

        $encryptionService = app(EncryptionService::class);

        foreach ($this->encrypts as $attribute => $context) {
            // Jika konteks tidak diberikan, gunakan nama atribut sebagai konteks
            if (is_numeric($attribute)) {
                $attribute = $context;
                $context = $attribute;
            }

            // Enkripsi hanya jika atribut memiliki nilai dan belum terenkripsi
            if (
                isset($this->attributes[$attribute]) &&
                !$this->isEncrypted($this->attributes[$attribute])
            ) {
                $this->attributes[$attribute] = $encryptionService->encrypt(
                    $this->attributes[$attribute],
                    $context
                );
            }
        }
    }

    /**
     * Dekripsi atribut-atribut yang ditentukan
     */
    protected function decryptAttributes()
    {
        if (empty($this->encrypts) || !is_array($this->encrypts)) {
            return;
        }

        $encryptionService = app(EncryptionService::class);

        foreach ($this->encrypts as $attribute => $context) {
            // Jika konteks tidak diberikan, gunakan nama atribut sebagai konteks
            if (is_numeric($attribute)) {
                $attribute = $context;
                $context = $attribute;
            }

            // Dekripsi hanya jika atribut memiliki nilai dan terenkripsi
            if (
                isset($this->attributes[$attribute]) &&
                $this->isEncrypted($this->attributes[$attribute])
            ) {
                try {
                    $this->attributes[$attribute] = $encryptionService->decrypt(
                        $this->attributes[$attribute],
                        $context
                    );
                } catch (\Exception $e) {
                    // Jika dekripsi gagal, biarkan nilainya tetap terenkripsi
                    // dan log error
                    \Log::error("Gagal mendekripsi atribut {$attribute}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Cek apakah string sudah terenkripsi
     * Metode sederhana untuk memeriksa apakah nilai terlihat seperti string terenkripsi dalam base64
     */
    protected function isEncrypted($value)
    {
        if (!is_string($value)) {
            return false;
        }

        // Cek apakah string adalah base64 valid dengan panjang minimal tertentu
        return preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $value) &&
            strlen($value) > 40;
    }

    /**
     * Khusus enkripsi atribut tertentu
     */
    public function encryptAttribute($attribute, $context = null)
    {
        if (!$context) {
            $context = $attribute;
        }

        $encryptionService = app(EncryptionService::class);

        if (
            isset($this->attributes[$attribute]) &&
            !$this->isEncrypted($this->attributes[$attribute])
        ) {
            $this->attributes[$attribute] = $encryptionService->encrypt(
                $this->attributes[$attribute],
                $context
            );
        }

        return $this;
    }

    /**
     * Khusus dekripsi atribut tertentu
     */
    public function decryptAttribute($attribute, $context = null)
    {
        if (!$context) {
            $context = $attribute;
        }

        $encryptionService = app(EncryptionService::class);

        if (
            isset($this->attributes[$attribute]) &&
            $this->isEncrypted($this->attributes[$attribute])
        ) {
            try {
                $this->attributes[$attribute] = $encryptionService->decrypt(
                    $this->attributes[$attribute],
                    $context
                );
            } catch (\Exception $e) {
                \Log::error("Gagal mendekripsi atribut {$attribute}: " . $e->getMessage());
            }
        }

        return $this;
    }
}
