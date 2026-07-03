<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use ZipArchive;

class GDriveDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gdrive:download {fileId : ID File dari link share Google Drive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unduh dan ekstrak dataset zip dari Google Drive langsung ke server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fileId = $this->argument('fileId');
        $this->info("Menghubungi Google Drive untuk mengunduh File ID: {$fileId}...");

        // Tentukan path penyimpanan zip sementara
        $tempDir = storage_path('app/temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }
        $zipPath = $tempDir . '/dataset.zip';

        try {
            $url = "https://docs.google.com/uc?export=download&id={$fileId}";
            
            // Request pertama untuk memicu cookie & mengambil halaman warning file besar jika ada
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
            ])->get($url);

            $body = $response->body();
            $confirmToken = null;

            // Cari token konfirmasi ukuran file besar menggunakan regex
            if (preg_match('/confirm=([A-Za-z0-9_-]+)/', $body, $matches)) {
                $confirmToken = $matches[1];
                $this->info("Menemukan token konfirmasi file besar: {$confirmToken}");
                $url = "https://docs.google.com/uc?export=download&confirm={$confirmToken}&id={$fileId}";
            }

            $this->info("Memulai pengunduhan file zip...");
            $this->output->progressStart(100);

            // Unduh file asli
            $downloadResponse = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
            ])
            ->timeout(600) // Timeout 10 menit
            ->get($url);

            if (!$downloadResponse->successful()) {
                $this->output->progressFinish();
                $this->error("\nGagal mengunduh file dari Google Drive. Pastikan file ID benar dan link di-set 'Anyone with the link' (Publik).");
                return 1;
            }

            File::put($zipPath, $downloadResponse->body());
            $this->output->progressAdvance(50);

            $this->info("\nFile berhasil diunduh. Mengekstrak...");

            // Target folder ekstraksi (public/dataset)
            $extractPath = public_path('dataset');
            if (!File::exists($extractPath)) {
                File::makeDirectory($extractPath, 0755, true);
            }

            $zip = new ZipArchive;
            if ($zip->open($zipPath) === TRUE) {
                $zip->extractTo($extractPath);
                $zip->close();
                $this->output->progressAdvance(50);
                
                // Hapus zip sementara
                File::delete($zipPath);

                $this->output->progressFinish();
                $this->info("Berhasil mengekstrak dataset ke: " . $extractPath);
                $this->info("Silakan masuk ke halaman Admin untuk melakukan sinkronisasi database.");
                return 0;
            } else {
                $this->output->progressFinish();
                $this->error("\nGagal mengekstrak file ZIP.");
                return 1;
            }

        } catch (\Exception $e) {
            $this->output->progressFinish();
            $this->error("\nTerjadi kesalahan: " . $e->getMessage());
            return 1;
        }
    }
}
