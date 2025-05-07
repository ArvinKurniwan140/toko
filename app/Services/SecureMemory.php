<?php

namespace App\Services;

class SecureMemory
{
    /**
     * Bersihkan string yang berisi data sensitif dari memori
     * 
     * @param string &$sensitiveData Referensi ke string yang berisi data sensitif
     */
    public static function cleanString(&$sensitiveData)
    {
        if (is_string($sensitiveData)) {
            $length = strlen($sensitiveData);

            // Tulis karakter acak untuk menimpa data di memori
            for ($i = 0; $i < $length; $i++) {
                $sensitiveData[$i] = chr(rand(0, 255));
            }

            // Tulis nol untuk menimpa data lagi
            for ($i = 0; $i < $length; $i++) {
                $sensitiveData[$i] = "\0";
            }

            // Hapus data dan set ke null
            $sensitiveData = null;
        }
    }

    /**
     * Bersihkan array yang berisi data sensitif dari memori
     * 
     * @param array &$sensitiveArray Referensi ke array yang berisi data sensitif
     */
    public static function cleanArray(&$sensitiveArray)
    {
        if (is_array($sensitiveArray)) {
            foreach ($sensitiveArray as &$value) {
                if (is_string($value)) {
                    self::cleanString($value);
                } elseif (is_array($value)) {
                    self::cleanArray($value);
                }
            }

            // Kosongkan array dan set ke null
            $sensitiveArray = [];
            $sensitiveArray = null;
        }
    }

    /**
     * Bersihkan buffer yang berisi data sensitif
     * 
     * @param resource &$buffer Referensi ke buffer yang berisi data sensitif
     */
    public static function cleanBuffer(&$buffer)
    {
        if (is_resource($buffer)) {
            // Posisikan pointer ke awal buffer
            rewind($buffer);

            // Dapatkan ukuran buffer
            $stat = fstat($buffer);
            $size = $stat['size'];

            // Timpa buffer dengan data acak
            $randomData = random_bytes($size);
            fwrite($buffer, $randomData);

            // Timpa buffer dengan nol
            $zeros = str_repeat("\0", $size);
            rewind($buffer);
            fwrite($buffer, $zeros);

            // Tutup buffer
            fclose($buffer);
            $buffer = null;
        }
    }

    /**
     * Hapus variabel dan picu garbage collector untuk membersihkan memori
     */
    public static function triggerGarbageCollection()
    {
        // Ini akan memaksa garbage collector untuk mengumpulkan variabel-variabel
        // yang sudah tidak direferensikan lagi
        gc_collect_cycles();
    }

    /**
     * Pembungkus untuk menjalankan callback dengan data sensitif dan membersihkannya setelah selesai
     * 
     * @param callable $callback Fungsi yang akan dijalankan
     * @param array $args Argumen-argumen untuk callback
     * @return mixed Hasil dari fungsi callback
     */
    public static function withSecureMemory(callable $callback, ...$args)
    {
        try {
            // Jalankan callback dengan argumen-argumen
            $result = $callback(...$args);
            return $result;
        } finally {
            // Bersihkan argumen-argumen
            foreach ($args as &$arg) {
                if (is_string($arg)) {
                    self::cleanString($arg);
                } elseif (is_array($arg)) {
                    self::cleanArray($arg);
                } elseif (is_resource($arg)) {
                    self::cleanBuffer($arg);
                }
            }

            // Picu garbage collector
            self::triggerGarbageCollection();
        }
    }
}
