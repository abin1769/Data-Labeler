<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use ZipArchive;

class KaggleDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kaggle:download {dataset : Ref dataset Kaggle (contoh: mostafaabla/garbage-classification)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unduh dan ekstrak dataset langsung dari Kaggle menggunakan API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dataset = $this->argument('dataset');
        $username = config('services.kaggle.username');
        $key = config('services.kaggle.key');

        if (!$username || !$key) {
            $this->error('Kredensial Kaggle belum diatur di file .env!');
            $this->info('Silakan tambahkan KAGGLE_USERNAME dan KAGGLE_KEY di file .env Anda.');
            return 1;
        }

        $this->info("Menghubungi Kaggle API untuk mengunduh dataset: {$dataset}...");

        // Deteksi apakah ini dataset biasa atau kompetisi
        if (str_contains($dataset, '/')) {
            // Standard dataset: owner/dataset-name
            $url = "https://www.kaggle.com/api/v1/datasets/download/{$dataset}";
            $this->info("Mendeteksi tipe: Kaggle Dataset");
        } else {
            // Competition dataset: competition-name
            $url = "https://www.kaggle.com/api/v1/competitions/data/download-all/{$dataset}";
            $this->info("Mendeteksi tipe: Kaggle Competition");
        }

        // Tentukan path penyimpanan zip sementara
        $tempDir = storage_path('app/temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }
        $zipPath = $tempDir . '/dataset.zip';

        $this->output->progressStart(100);

        try {
            // Lakukan request dengan HTTP Basic Auth
            $response = Http::withBasicAuth($username, $key)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                ])
                ->timeout(300) // Timeout 5 menit untuk file besar
                ->get($url);

            if (!$response->successful()) {
                $this->output->progressFinish();
                $this->error("\nGagal mengunduh dataset. Kode Status: " . $response->status());
                $this->error("Pastikan nama dataset benar dan kredensial API Anda valid.");
                return 1;
            }

            File::put($zipPath, $response->body());
            $this->output->progressAdvance(50);

            $this->info("\nDataset berhasil diunduh. Mengekstrak file...");

            // Target folder ekstraksi
            $extractPath = public_path('dataset');
            if (!File::exists($extractPath)) {
                File::makeDirectory($extractPath, 0755, true);
            }

            // Ekstrak ZIP
            $zip = new ZipArchive;
            if ($zip->open($zipPath) === TRUE) {
                $zip->extractTo($extractPath);
                $zip->close();
                $this->output->progressAdvance(50);
                
                // Hapus zip sementara
                File::delete($zipPath);
                
                $this->output->progressFinish();
                $this->info("Berhasil diekstraksi ke: " . $extractPath);
                $this->info("Silakan masuk ke menu Admin untuk menyinkronkan database.");
                return 0;
            } else {
                $this->output->progressFinish();
                $this->error("\nGagal membuka file ZIP dataset.");
                return 1;
            }

        } catch (\Exception $e) {
            $this->output->progressFinish();
            $this->error("\nTerjadi kesalahan: " . $e->getMessage());
            return 1;
        }
    }
}
