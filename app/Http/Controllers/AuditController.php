<?php

namespace App\Http\Controllers;

use App\Models\AuditCandidate;
use App\Models\WorkspaceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ZipArchive;

/**
 * Menangani alur audit kandidat salah-label (hasil scripts/audit_data.py di
 * project Python) -- dua putaran:
 *
 *   Putaran 1: tinjau kandidat yang di-flag cleanlab, putuskan A (salah
 *   label) / B (kontaminasi) / C (ambigu) / D (model error).
 *
 *   Putaran 2: untuk yang dikonfirmasi A, tentukan kelas tujuan yang benar
 *   (atau CONTAMINATION kalau ternyata bukan salah label).
 *
 * Reuse sesi nickname/prodi yang sama dengan DatasetController, dan pola
 * lock-for-update + 409-on-conflict yang sama dengan submitLabel() --
 * bukan sistem klaim custom, supaya konsisten dengan konvensi app ini.
 */
class AuditController extends Controller
{
    private function getWorkspaceSetting(): WorkspaceSetting
    {
        $setting = WorkspaceSetting::query()->first();

        if ($setting) {
            return $setting;
        }

        return WorkspaceSetting::create([
            'active_activity' => WorkspaceSetting::ACTIVE_LABELING,
            'access_passkey' => strtoupper(bin2hex(random_bytes(4))),
        ]);
    }

    private function isAuditAccessValid(Request $request): bool
    {
        $setting = $this->getWorkspaceSetting();
        $sessionPasskey = (string) $request->session()->get('workspace_passkey', '');

        return $sessionPasskey !== ''
            && hash_equals((string) $setting->access_passkey, $sessionPasskey)
            && $setting->active_activity === WorkspaceSetting::ACTIVE_AUDIT;
    }

    private function deniedAuditResponse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => $message], 403);
        }

        return redirect()->route('home')->with('error', $message);
    }

    /**
     * Halaman putaran 1: tinjau kandidat, putuskan A/B/C/D.
     */
    public function index(Request $request)
    {
        if (!$request->session()->has('nickname') || !$this->isAuditAccessValid($request)) {
            return redirect()->route('home')->with('error', 'Workspace audit belum aktif atau passkey sudah tidak valid.');
        }

        return view('audit', [
            'nickname' => $request->session()->get('nickname'),
            'prodi' => $request->session()->get('prodi'),
            'rubric' => AuditCandidate::ROUND1_RUBRIC,
        ]);
    }

    /**
     * Halaman putaran 2: tentukan kelas tujuan yang benar untuk kandidat A.
     */
    public function relabelIndex(Request $request)
    {
        if (!$request->session()->has('nickname') || !$this->isAuditAccessValid($request)) {
            return redirect()->route('home')->with('error', 'Workspace audit belum aktif atau passkey sudah tidak valid.');
        }

        return view('audit_relabel', [
            'nickname' => $request->session()->get('nickname'),
            'prodi' => $request->session()->get('prodi'),
            'classOptions' => AuditCandidate::CLASS_OPTIONS,
            'contaminationDecision' => AuditCandidate::CONTAMINATION_DECISION,
        ]);
    }

    /**
     * Serve gambar audit langsung dari data/train/{label}/{filename} (fallback kalau
     * public/dataset/train tidak diserve langsung oleh web server).
     */
    public function serveImage($label, $filename)
    {
        $label = basename($label);
        $filename = basename($filename);
        $filePath = public_path('dataset' . DIRECTORY_SEPARATOR . 'train' . DIRECTORY_SEPARATOR . $label . DIRECTORY_SEPARATOR . $filename);

        if (!File::exists($filePath)) {
            abort(404, 'Gambar tidak ditemukan di folder dataset/train.');
        }

        return response()->file($filePath, [
            'Cache-Control' => 'public, max-age=86400, must-revalidate',
        ]);
    }

    /**
     * Ambil kandidat putaran 1 berikutnya (belum ada round1_decision).
     *
     * predicted_label & label_quality_score SENGAJA tidak ikut di respons ini --
     * baru dikembalikan setelah submit (lihat submitDecision), supaya reviewer
     * menilai independen dulu sebelum lihat tebakan model (hindari anchoring bias).
     */
    public function getNextCandidate(Request $request)
    {
        if (!$this->isAuditAccessValid($request)) {
            return $this->deniedAuditResponse($request, 'Workspace audit belum aktif atau passkey sudah tidak valid.');
        }

        $nickname = $request->session()->get('nickname');

        $stats = AuditCandidate::selectRaw("
            COUNT(CASE WHEN round1_decision IS NULL THEN 1 END) as total_left,
            COUNT(CASE WHEN round1_decision IS NOT NULL THEN 1 END) as total_done
        ")->first();

        $totalLeft = (int) $stats->total_left;
        $totalDone = (int) $stats->total_done;

        $candidate = null;
        if ($totalLeft > 0) {
            $offset = rand(0, $totalLeft - 1);
            $candidate = AuditCandidate::whereNull('round1_decision')->skip($offset)->first();
        }

        if (!$candidate) {
            return response()->json(['completed' => true, 'total_left' => 0, 'total_done' => $totalDone]);
        }

        return response()->json([
            'completed' => false,
            'candidate' => [
                'id' => $candidate->id,
                'filename' => $candidate->filename,
                'given_label' => $candidate->given_label,
                'url' => $candidate->url,
            ],
            'total_left' => $totalLeft,
            'total_done' => $totalDone,
        ]);
    }

    /**
     * Submit keputusan putaran 1 (A/B/C/D).
     */
    public function submitDecision(Request $request)
    {
        if (!$this->isAuditAccessValid($request)) {
            return $this->deniedAuditResponse($request, 'Workspace audit belum aktif atau passkey sudah tidak valid.');
        }

        $nickname = $request->session()->get('nickname');

        $request->validate([
            'candidate_id' => 'required|exists:audit_candidates,id',
            'decision' => 'required|in:A,B,C,D',
            'note' => 'nullable|string|max:500',
        ]);

        $id = $request->input('candidate_id');
        $decision = $request->input('decision');
        $note = $request->input('note');

        $result = DB::transaction(function () use ($id, $decision, $note, $nickname) {
            $candidate = AuditCandidate::where('id', $id)
                ->whereNull('round1_decision')
                ->lockForUpdate()
                ->first();

            if (!$candidate) {
                return null;
            }

            $candidate->update([
                'round1_decision' => $decision,
                'round1_note' => $note,
                'round1_by' => $nickname,
                'round1_at' => now(),
            ]);

            return $candidate;
        });

        if (!$result) {
            return response()->json(['error' => 'Gambar ini sudah ditinjau orang lain. Mengambil gambar berikutnya.'], 409);
        }

        return response()->json([
            'success' => true,
            'predicted_label' => $result->predicted_label,
            'label_quality_score' => $result->label_quality_score,
        ]);
    }

    /**
     * Ambil kandidat putaran 2 berikutnya (round1_decision == 'A', round2_decision belum diisi).
     */
    public function getNextRelabel(Request $request)
    {
        if (!$this->isAuditAccessValid($request)) {
            return $this->deniedAuditResponse($request, 'Workspace audit belum aktif atau passkey sudah tidak valid.');
        }

        $nickname = $request->session()->get('nickname');

        $base = AuditCandidate::where('round1_decision', 'A')->whereNull('round2_decision');

        $totalLeft = (clone $base)->count();
        $totalDone = AuditCandidate::where('round1_decision', 'A')->whereNotNull('round2_decision')->count();

        $candidate = null;
        if ($totalLeft > 0) {
            $offset = rand(0, $totalLeft - 1);
            $candidate = (clone $base)->skip($offset)->first();
        }

        if (!$candidate) {
            return response()->json(['completed' => true, 'total_left' => 0, 'total_done' => $totalDone]);
        }

        return response()->json([
            'completed' => false,
            'candidate' => [
                'id' => $candidate->id,
                'filename' => $candidate->filename,
                'given_label' => $candidate->given_label,
                'url' => $candidate->url,
            ],
            'total_left' => $totalLeft,
            'total_done' => $totalDone,
        ]);
    }

    /**
     * Submit keputusan putaran 2 (kelas tujuan yang benar, atau CONTAMINATION).
     */
    public function submitRelabel(Request $request)
    {
        if (!$this->isAuditAccessValid($request)) {
            return $this->deniedAuditResponse($request, 'Workspace audit belum aktif atau passkey sudah tidak valid.');
        }

        $nickname = $request->session()->get('nickname');

        $validOptions = array_merge(array_keys(AuditCandidate::CLASS_OPTIONS), [AuditCandidate::CONTAMINATION_DECISION]);

        $request->validate([
            'candidate_id' => 'required|exists:audit_candidates,id',
            'decision' => 'required|in:' . implode(',', $validOptions),
        ]);

        $id = $request->input('candidate_id');
        $decision = $request->input('decision');

        $result = DB::transaction(function () use ($id, $decision, $nickname) {
            $candidate = AuditCandidate::where('id', $id)
                ->where('round1_decision', 'A')
                ->whereNull('round2_decision')
                ->lockForUpdate()
                ->first();

            if (!$candidate) {
                return null;
            }

            $candidate->update([
                'round2_decision' => $decision,
                'round2_by' => $nickname,
                'round2_at' => now(),
            ]);

            return $candidate;
        });

        if (!$result) {
            return response()->json(['error' => 'Gambar ini sudah ditinjau orang lain. Mengambil gambar berikutnya.'], 409);
        }

        return response()->json([
            'success' => true,
            'predicted_label' => $result->predicted_label,
            'label_quality_score' => $result->label_quality_score,
        ]);
    }

    /**
     * Admin: dashboard audit -- stats, upload CSV kandidat, upload ZIP gambar train, unduh hasil.
     */
    public function adminView(Request $request)
    {
        if (!$request->session()->has('admin_authenticated')) {
            return redirect()->route('admin');
        }

        $stats = [
            'total' => AuditCandidate::count(),
            'round1_pending' => AuditCandidate::whereNull('round1_decision')->count(),
            'round1_by_decision' => AuditCandidate::whereNotNull('round1_decision')
                ->select('round1_decision', DB::raw('count(*) as total'))
                ->groupBy('round1_decision')
                ->pluck('total', 'round1_decision'),
            'round2_pending' => AuditCandidate::where('round1_decision', 'A')->whereNull('round2_decision')->count(),
            'round2_done' => AuditCandidate::where('round1_decision', 'A')->whereNotNull('round2_decision')->count(),
        ];

        return view('admin_audit', compact('stats'));
    }

    /**
     * Import label_issues.csv (dari scripts/audit_data.py) -- cuma baris
     * is_flagged_label_issue=True yang diimpor. updateOrCreate per filename
     * supaya aman diupload ulang (tidak menimpa round1/round2 yang sudah ada).
     */
    public function uploadCandidatesCsv(Request $request)
    {
        $request->validate([
            'candidates_csv' => 'required|file|mimes:csv,txt|max:20480',
        ]);

        $handle = fopen($request->file('candidates_csv')->getRealPath(), 'r');
        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return back()->with('error', 'File CSV kosong atau tidak valid.');
        }
        $col = array_flip($header);

        $required = ['filepath', 'given_label', 'predicted_label', 'label_quality_score', 'is_flagged_label_issue'];
        foreach ($required as $c) {
            if (!isset($col[$c])) {
                fclose($handle);
                return back()->with('error', "Kolom '{$c}' tidak ada di CSV. Pastikan ini label_issues.csv dari scripts/audit_data.py.");
            }
        }

        $imported = 0;
        $skipped = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $flagged = strtolower(trim($row[$col['is_flagged_label_issue']])) === 'true';
            if (!$flagged) {
                $skipped++;
                continue;
            }

            $filepath = str_replace('\\', '/', $row[$col['filepath']]);
            $filename = basename($filepath);

            AuditCandidate::updateOrCreate(
                ['filename' => $filename],
                [
                    'given_label' => $row[$col['given_label']],
                    'predicted_label' => $row[$col['predicted_label']],
                    'label_quality_score' => (float) $row[$col['label_quality_score']],
                ]
            );
            $imported++;
        }
        fclose($handle);

        return back()->with('success', "Berhasil impor {$imported} kandidat dari CSV ({$skipped} baris dilewati karena tidak di-flag).");
    }

    /**
     * Upload ZIP berisi struktur folder data/train/{0_Recyclable,1_Electronic,2_Organic}/
     * -- diekstrak ke public/dataset/train/ (dipertahankan struktur foldernya oleh ZipArchive).
     */
    public function uploadTrainZip(Request $request)
    {
        set_time_limit(360);
        ini_set('memory_limit', '512M');

        $request->validate([
            'train_zip' => 'required|file|mimes:zip|max:2048000', // Max 2GB
        ]);

        $file = $request->file('train_zip');
        $tempDir = storage_path('app' . DIRECTORY_SEPARATOR . 'temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $tempZipPath = $tempDir . DIRECTORY_SEPARATOR . 'train_upload_' . time() . '.zip';
        $file->move($tempDir, basename($tempZipPath));

        $extractPath = public_path('dataset' . DIRECTORY_SEPARATOR . 'train');
        if (!File::exists($extractPath)) {
            File::makeDirectory($extractPath, 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($tempZipPath) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
            File::delete($tempZipPath);
            return back()->with('success', "ZIP gambar train berhasil diekstrak ke dataset/train/.");
        }

        File::delete($tempZipPath);
        return back()->with('error', "Gagal membuka atau mengekstrak file ZIP.");
    }

    /**
     * Unduh hasil putaran 1 -- bentuknya PERSIS sama dengan label_review.csv dari
     * scripts/review_labels.py, supaya langsung bisa dipakai
     * scripts/remove_contamination.py tanpa perlu diolah lagi.
     */
    public function downloadRound1Csv()
    {
        $rows = AuditCandidate::whereNotNull('round1_decision')->orderBy('filename')->get();
        return $this->streamCsv('label_review', $rows, function ($row) {
            return [
                'data\\train\\' . $row->given_label . '\\' . $row->filename,
                $row->given_label,
                $row->predicted_label,
                $row->label_quality_score,
                $row->round1_by,
                $row->round1_decision,
                $row->round1_note,
                optional($row->round1_at)->format('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * Unduh hasil putaran 2 -- bentuknya PERSIS sama dengan relabel_review.csv dari
     * scripts/relabel_confirmed.py, supaya langsung bisa dipakai
     * scripts/execute_relabel.py & scripts/remove_contamination.py --extra-review-csv.
     */
    public function downloadRound2Csv()
    {
        $rows = AuditCandidate::whereNotNull('round2_decision')->orderBy('filename')->get();
        return $this->streamCsv('relabel_review', $rows, function ($row) {
            return [
                'data\\train\\' . $row->given_label . '\\' . $row->filename,
                $row->given_label,
                $row->predicted_label,
                $row->label_quality_score,
                $row->round2_by,
                $row->round2_decision,
                '',
                optional($row->round2_at)->format('Y-m-d H:i:s'),
            ];
        });
    }

    private function streamCsv(string $name, $rows, callable $toRow)
    {
        $filename = $name . '_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ];

        $callback = function () use ($rows, $toRow) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['filepath', 'given_label', 'predicted_label', 'label_quality_score', 'reviewer', 'decision', 'note', 'reviewed_at']);
            foreach ($rows as $row) {
                fputcsv($file, $toRow($row));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
