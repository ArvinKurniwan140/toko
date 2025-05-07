<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateAESKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encryption:generate-key 
    {--length=32 : Panjang kunci dalam bytes (32 bytes = 256 bit)}
    {--context= : Konteks penyimpanan kunci (default, payment, personal, dsb)}
    {--save : Simpan kunci ke file .env.keys}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate kunci enkripsi AES-256 yang aman';

    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $length = $this->option('length');
        $context = $this->option('context') ?: 'default';
        $saveToFile = $this->option('save');

        // Generate kunci secara aman menggunakan random bytes
        $key = bin2hex(openssl_random_pseudo_bytes($length));

        // Format kunci untuk disimpan di file .env
        $envKey = strtoupper($context) . '_ENCRYPTION_KEY=' . $key;

        // Tampilkan kunci ke konsol
        $this->info('Kunci AES-256 berhasil dibuat untuk konteks: ' . $context);
        $this->line($key);

        // Simpan ke file jika diminta
        if ($saveToFile) {
            $envKeysPath = base_path('.env.keys');

            if (File::exists($envKeysPath)) {
                // Tambahkan ke file yang sudah ada
                File::append($envKeysPath, "\n" . $envKey);
                $this->info('Kunci berhasil ditambahkan ke file .env.keys');
            } else {
                // Buat file baru
                File::put($envKeysPath, $envKey);
                $this->info('Kunci berhasil disimpan ke file .env.keys');
                $this->warn('PERINGATAN: Pastikan file .env.keys ditambahkan ke .gitignore!');
            }

            $this->info('Gunakan kunci ini di file .env atau sistem manajemen kunci Anda.');
        }

        $this->info('Pastikan kunci disimpan di tempat yang aman dan tidak dibagikan!');
    }

    /**
     * Generate a cryptographically secure random key
     */
}
