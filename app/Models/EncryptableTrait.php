<?php

namespace App\Models\Traits;

use App\Services\EncryptionService;
use Illuminate\Support\Facades\Log;

trait Encryptable
{
    /**
     * Boot the trait
     */
    protected static function bootEncryptable()
    {
        // Hook sebelum model disimpan
        static::saving(function ($model) {
            $model->encryptAttributes();
        });

        // Hook setelah model diambil dari database
        static::retrieved(function ($model) {
            $model->decryptAttributes();
        });
    }

    /**
     * Enkripsi atribut yang telah ditandai untuk enkripsi
     */
    protected function encryptAttributes()
    {
        if (!property_exists($this, 'encryptable') || !is_array($this->encryptable)) {
            return;
        }

        $encryptionService = app(EncryptionService::class);

        foreach ($this->encryptable as $attribute) {
            if (isset($this->attributes[$attribute]) && !empty($this->attributes[$attribute])) {
                // Enkripsi hanya jika atribut ada dan tidak kosong
                $this->attributes[$attribute] = $encryptionService->encrypt($this->attributes[$attribute]);
            }
        }
    }

    /**
     * Dekripsi atribut yang telah dienkripsi
     */
    protected function decryptAttributes()
    {
        if (!property_exists($this, 'encryptable') || !is_array($this->encryptable)) {
            return;
        }

        $encryptionService = app(EncryptionService::class);

        foreach ($this->encryptable as $attribute) {
            if (isset($this->attributes[$attribute]) && !empty($this->attributes[$attribute])) {
                try {
                    // Dekripsi hanya jika atribut ada dan tidak kosong
                    $decrypted = $encryptionService->decrypt($this->attributes[$attribute]);

                    // Setel nilai atribut ke hasil dekripsi jika berhasil
                    if ($decrypted !== null) {
                        $this->attributes[$attribute] = $decrypted;
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to decrypt {$attribute} on " . get_class($this) . ": " . $e->getMessage());
                    // Jangan ubah nilai atribut jika dekripsi gagal
                }
            }
        }
    }

    /**
     * Dekripsi satu atribut spesifik
     * 
     * @param string $attribute
     * @return mixed
     */
    public function getDecryptedAttribute($attribute)
    {
        if (!property_exists($this, 'encryptable') || !is_array($this->encryptable) || !in_array($attribute, $this->encryptable)) {
            return $this->attributes[$attribute] ?? null;
        }

        $encryptionService = app(EncryptionService::class);

        if (isset($this->attributes[$attribute]) && !empty($this->attributes[$attribute])) {
            try {
                return $encryptionService->decrypt($this->attributes[$attribute]);
            } catch (\Exception $e) {
                Log::error("Failed to decrypt {$attribute}: " . $e->getMessage());
                return null;
            }
        }

        return null;
    }

    /**
     * Enkripsi ulang semua data setelah rotasi kunci
     * 
     * @return bool
     */
    public static function reEncryptAllAfterKeyRotation()
    {
        $model = new static;

        if (!property_exists($model, 'encryptable') || !is_array($model->encryptable)) {
            return true;
        }

        $encryptionService = app(EncryptionService::class);

        foreach ($model->encryptable as $attribute) {
            try {
                // Ambil semua data yang perlu dienkripsi ulang
                $records = $model->select(['id', $attribute])->get();

                $dataToReEncrypt = [];
                foreach ($records as $record) {
                    if (!empty($record->{$attribute})) {
                        $dataToReEncrypt[$record->id] = $record->{$attribute};
                    }
                }

                // Enkripsi ulang data
                $reEncryptedData = $encryptionService->reEncryptAfterRotation($dataToReEncrypt);

                // Simpan data terenkripsi kembali ke database
                foreach ($reEncryptedData as $id => $encrypted) {
                    $model->where('id', $id)->update([$attribute => $encrypted]);
                }

                Log::info("Successfully re-encrypted {$attribute} for " . count($dataToReEncrypt) . " records");
            } catch (\Exception $e) {
                Log::error("Failed to re-encrypt {$attribute}: " . $e->getMessage());
                return false;
            }
        }

        return true;
    }
}
