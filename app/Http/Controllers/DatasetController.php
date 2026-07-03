<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use ZipArchive;

class DatasetController extends Controller
{
    /**
     * Get the dataset path from configuration.
     */
    private function getDatasetPath()
    {
        $path = env('DATASET_PATH');
        if (!$path) {
            // Check if public/dataset/test exists (e.g. from Kaggle dataset structure), else fallback to public/dataset
            $testPath = public_path('dataset' . DIRECTORY_SEPARATOR . 'test');
            $path = File::exists($testPath) ? $testPath : public_path('dataset');
        }
        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Landing page for setting nickname or redirecting to labeling interface.
     */
    public function index(Request $request)
    {
        if ($request->session()->has('nickname')) {
            $examples = [
                0 => [],
                1 => [],
                2 => []
            ];

            // 1. Coba ambil dari database ExampleImage (yang diunggah ke volume persisten)
            foreach ([0, 1, 2] as $label) {
                $dbExamples = \App\Models\ExampleImage::where('label', $label)->get();
                if ($dbExamples->isNotEmpty()) {
                    // Let's pass all urls so JS can cycle them!
                    foreach ($dbExamples as $dbEx) {
                        $examples[$label][] = $dbEx->url;
                    }
                }
            }

            // 2. Jika folder manual kosong, baru fallback ke folder train dataset (public/dataset/train/)
            $trainPath = public_path('dataset' . DIRECTORY_SEPARATOR . 'train');
            if (File::exists($trainPath)) {
                $directories = File::directories($trainPath);
                
                foreach ([0, 1, 2] as $label) {
                    if (empty($examples[$label])) {
                        $targetDir = null;
                        foreach ($directories as $dir) {
                            $dirName = basename($dir);
                            if (str_starts_with($dirName, $label . '_')) {
                                $targetDir = $dir;
                                break;
                            }
                        }

                        if ($targetDir && File::exists($targetDir)) {
                            $files = File::files($targetDir);
                            $imageFiles = array_filter($files, function($file) {
                                return in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                            });

                            if (!empty($imageFiles)) {
                                shuffle($imageFiles); // Shuffle agar dinamis
                                $selectedFiles = array_slice($imageFiles, 0, 4);

                                foreach ($selectedFiles as $file) {
                                    $folderName = basename($targetDir);
                                    $examples[$label][] = asset("dataset/train/{$folderName}/" . $file->getFilename());
                                }
                            }
                        }
                    }
                }
            }

            return view('label', [
                'nickname' => $request->session()->get('nickname'),
                'prodi' => $request->session()->get('prodi'),
                'examples' => $examples
            ]);
        }

        return view('nickname');
    }

    /**
     * Set user nickname and prodi in session.
     */
    public function setNickname(Request $request)
    {
        $request->validate([
            'nickname' => 'required|string|max:50|regex:/^[a-zA-Z0-9_\s\-]+$/',
            'prodi' => 'required|string|max:100|regex:/^[a-zA-Z0-9_\s\-\.]+$/'
        ], [
            'nickname.regex' => 'Nama/nickname hanya boleh mengandung huruf, angka, spasi, garis bawah, dan tanda hubung.',
            'prodi.regex' => 'Program studi hanya boleh mengandung huruf, angka, spasi, titik, garis bawah, dan tanda hubung.'
        ]);

        $nickname = trim($request->input('nickname'));
        $prodi = trim($request->input('prodi'));

        $request->session()->put('nickname', $nickname);
        $request->session()->put('prodi', $prodi);

        return redirect()->route('home');
    }

    /**
     * Clear nickname and prodi session.
     */
    public function logoutNickname(Request $request)
    {
        $request->session()->forget(['nickname', 'prodi']);
        return redirect()->route('home');
    }

    /**
     * Serve an image file from the dataset path.
     */
    public function serveImage($filename)
    {
        // Prevent directory traversal attacks
        $filename = basename($filename);
        $datasetPath = $this->getDatasetPath();
        $filePath = $datasetPath . DIRECTORY_SEPARATOR . $filename;

        if (!File::exists($filePath)) {
            abort(404, 'Image not found in dataset folder.');
        }

        // Stream file directly (much faster and memory-friendly)
        return response()->file($filePath, [
            'Cache-Control' => 'public, max-age=86400, must-revalidate',
        ]);
    }

    /**
     * Get the next available unlabeled image for labeling.
     */
    public function getNextImage(Request $request)
    {
        $nickname = $request->session()->get('nickname');
        if (!$nickname) {
            return response()->json(['error' => 'Nickname not set'], 403);
        }

        // Calculate all statistics in a single highly optimized SQL query
        $stats = Image::selectRaw("
            COUNT(CASE WHEN label_status = 'unlabeled' THEN 1 END) as total_left,
            COUNT(CASE WHEN label_status IN ('pending', 'approved') THEN 1 END) as total_labeled,
            COUNT(CASE WHEN labeled_by = ? AND label_status IN ('pending', 'approved') THEN 1 END) as user_labeled
        ", [$nickname])->first();

        $totalLeft = (int) $stats->total_left;
        $totalLabeled = (int) $stats->total_labeled;
        $userLabeled = (int) $stats->user_labeled;

        // Fetch one random unlabeled image using skip-offset (extremely fast on Postgres compared to inRandomOrder)
        $image = null;
        if ($totalLeft > 0) {
            $offset = rand(0, $totalLeft - 1);
            $image = Image::where('label_status', 'unlabeled')
                ->skip($offset)
                ->first();
        }

        if (!$image) {
            return response()->json([
                'completed' => true,
                'total_left' => 0,
                'total_labeled' => $totalLabeled,
                'user_labeled' => $userLabeled
            ]);
        }

        return response()->json([
            'completed' => false,
            'image' => [
                'id' => $image->id,
                'filename' => $image->filename,
                'url' => $image->url
            ],
            'total_left' => $totalLeft,
            'total_labeled' => $totalLabeled,
            'user_labeled' => $userLabeled
        ]);
    }

    /**
     * Submit a label for an image.
     */
    public function submitLabel(Request $request)
    {
        $nickname = $request->session()->get('nickname');
        $prodi = $request->session()->get('prodi');
        if (!$nickname || !$prodi) {
            return response()->json(['error' => 'Sesi Anda telah berakhir, silakan muat ulang halaman.'], 403);
        }

        $request->validate([
            'image_id' => 'required|exists:images,id',
            'label' => 'required|in:0,1,2'
        ]);

        $imageId = $request->input('image_id');
        $label = $request->input('label');

        $updated = DB::transaction(function () use ($imageId, $label, $nickname, $prodi) {
            // Find and lock the image for update to prevent race conditions
            $image = Image::where('id', $imageId)
                ->where('label_status', 'unlabeled')
                ->lockForUpdate()
                ->first();

            if (!$image) {
                return false;
            }

            $image->update([
                'label' => $label,
                'labeled_by' => $nickname,
                'prodi' => $prodi,
                'label_status' => 'pending'
            ]);

            return true;
        });

        if (!$updated) {
            return response()->json(['error' => 'This image was already labeled by someone else! Fetching next image.'], 409);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Fetch real-time leaderboard data.
     */
    public function getLeaderboard()
    {
        $leaderboard = Image::select('labeled_by', DB::raw('count(*) as total'))
            ->whereIn('label_status', ['pending', 'approved'])
            ->whereNotNull('labeled_by')
            ->groupBy('labeled_by')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        return response()->json($leaderboard);
    }

    /**
     * Admin view: login or dashboard.
     */
    public function adminView(Request $request)
    {
        if ($request->session()->has('admin_authenticated')) {
            // Calculate stats
            $stats = [
                'total' => Image::count(),
                'unlabeled' => Image::where('label_status', 'unlabeled')->count(),
                'pending' => Image::where('label_status', 'pending')->count(),
                'approved' => Image::where('label_status', 'approved')->count(),
                'recycleable' => Image::where('label', 0)->where('label_status', 'approved')->count(),
                'electronics' => Image::where('label', 1)->where('label_status', 'approved')->count(),
                'organics' => Image::where('label', 2)->where('label_status', 'approved')->count(),
            ];

            // Get unique labeler names that have pending items
            $pendingLabelers = Image::where('label_status', 'pending')
                ->whereNotNull('labeled_by')
                ->distinct()
                ->pluck('labeled_by')
                ->toArray();

            // Get unique labeler names that have approved items
            $approvedLabelers = Image::where('label_status', 'approved')
                ->whereNotNull('labeled_by')
                ->distinct()
                ->pluck('labeled_by')
                ->toArray();

            // PENDING QUERY (with Filter & Search)
            $filterUser = $request->query('filter_user');
            $searchPending = $request->query('search_pending');
            
            $pendingQuery = Image::where('label_status', 'pending');
            if ($filterUser) {
                $pendingQuery->where('labeled_by', $filterUser);
            }
            if ($searchPending) {
                // Remove potential .jpg extension to search by ID or filename
                $cleanSearchPending = str_ireplace(['.jpg', '.jpeg', '.png', '.webp'], '', $searchPending);
                $pendingQuery->where(function($q) use ($searchPending, $cleanSearchPending) {
                    $q->where('filename', 'like', '%' . $searchPending . '%')
                      ->orWhere('filename', 'like', '%' . $cleanSearchPending . '%');
                });
            }
            $pendingItems = $pendingQuery->orderBy('updated_at', 'asc')
                ->paginate(20, ['*'], 'pending_page');

            // APPROVED QUERY (with Filter & Search)
            $filterApprovedUser = $request->query('filter_approved_user');
            $searchApproved = $request->query('search_approved');

            $approvedQuery = Image::where('label_status', 'approved');
            if ($filterApprovedUser) {
                $approvedQuery->where('labeled_by', $filterApprovedUser);
            }
            if ($searchApproved) {
                $cleanSearchApproved = str_ireplace(['.jpg', '.jpeg', '.png', '.webp'], '', $searchApproved);
                $approvedQuery->where(function($q) use ($searchApproved, $cleanSearchApproved) {
                    $q->where('filename', 'like', '%' . $searchApproved . '%')
                      ->orWhere('filename', 'like', '%' . $cleanSearchApproved . '%');
                });
            }
            $approvedItems = $approvedQuery->orderBy('updated_at', 'desc')
                ->paginate(20, ['*'], 'approved_page');

            // Get database examples list for admin to delete/manage (stored on persistent volume)
            $manualExamples = [
                0 => [],
                1 => [],
                2 => []
            ];
            $dbExamples = \App\Models\ExampleImage::orderBy('created_at', 'desc')->get();
            foreach ($dbExamples as $dbEx) {
                $manualExamples[$dbEx->label][] = [
                    'id' => $dbEx->id,
                    'filename' => $dbEx->filename,
                    'url' => $dbEx->url
                ];
            }

            return view('admin', compact(
                'stats', 'pendingItems', 'approvedItems', 'manualExamples', 
                'pendingLabelers', 'filterUser', 'searchPending',
                'approvedLabelers', 'filterApprovedUser', 'searchApproved'
            ));
        }

        return view('admin_login');
    }

    /**
     * Admin login handling.
     */
    public function adminLogin(Request $request)
    {
        $password = $request->input('password');
        $correctPassword = env('ADMIN_PASSWORD', 'admin123');

        if ($password === $correctPassword) {
            $request->session()->put('admin_authenticated', true);
            return redirect()->route('admin');
        }

        return back()->withErrors(['password' => 'Password salah!']);
    }

    /**
     * Admin logout.
     */
    public function adminLogout(Request $request)
    {
        $request->session()->forget('admin_authenticated');
        return redirect()->route('admin');
    }

    /**
     * Internal helper to scan dataset path and register new images.
     */
    private function syncDatasetInternal()
    {
        $datasetPath = $this->getDatasetPath();

        if (!File::exists($datasetPath)) {
            throw new \Exception("Dataset folder not found at: {$datasetPath}. Please verify your .env file or upload a ZIP.");
        }

        // Scan folder for images (jpg, jpeg, png, gif, webp)
        $files = File::files($datasetPath);
        $supportedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $imageFiles = [];

        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, $supportedExtensions)) {
                $imageFiles[] = $file->getFilename();
            }
        }

        if (empty($imageFiles)) {
            return 0;
        }

        $insertedCount = 0;
        $existingFiles = Image::pluck('filename')->toArray();
        $existingFilesMap = array_flip($existingFiles);

        // Batch inserts for performance
        $chunks = array_chunk($imageFiles, 200);
        foreach ($chunks as $chunk) {
            $insertData = [];
            foreach ($chunk as $filename) {
                if (!isset($existingFilesMap[$filename])) {
                    $insertData[] = [
                        'filename' => $filename,
                        'label' => null,
                        'labeled_by' => null,
                        'label_status' => 'unlabeled',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if (!empty($insertData)) {
                Image::insert($insertData);
                $insertedCount += count($insertData);
            }
        }

        return $insertedCount;
    }

    /**
     * Scan the local dataset directory and sync it with the database.
     */
    public function syncDataset()
    {
        try {
            $insertedCount = $this->syncDatasetInternal();
            return back()->with('success', "Dataset synced! Added {$insertedCount} new images. Total images: " . Image::count());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Handle ZIP dataset upload, unzipping, and auto-sync.
     */
    public function uploadDataset(Request $request)
    {
        set_time_limit(360);
        ini_set('memory_limit', '512M');

        $request->validate([
            'dataset_zip' => 'required|file|mimes:zip|max:204800' // Max 200MB ZIP
        ]);

        $file = $request->file('dataset_zip');
        $tempDir = storage_path('app' . DIRECTORY_SEPARATOR . 'temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }
        
        $tempZipPath = $tempDir . DIRECTORY_SEPARATOR . 'upload_' . time() . '.zip';
        $file->move($tempDir, basename($tempZipPath));

        $extractPath = $this->getDatasetPath();
        if (!File::exists($extractPath)) {
            File::makeDirectory($extractPath, 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($tempZipPath) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
            File::delete($tempZipPath);

            // Automatically run sync
            try {
                $insertedCount = $this->syncDatasetInternal();
                return back()->with('success', "Dataset ZIP berhasil diunggah & diekstrak! Menyinkronkan {$insertedCount} gambar baru ke database. Total gambar di database: " . Image::count());
            } catch (\Exception $e) {
                return back()->with('success', "Dataset ZIP berhasil diekstrak, namun gagal sinkron otomatis: " . $e->getMessage());
            }
        } else {
            File::delete($tempZipPath);
            return back()->with('error', "Gagal membuka atau mengekstrak file ZIP.");
        }
    }

    /**
     * Handle example images upload for visual guidelines.
     */
    public function uploadExamples(Request $request)
    {
        $request->validate([
            'label' => 'required|in:0,1,2',
            'example_images' => 'required|array',
            'example_images.*' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:10240' // Max 10MB per image
        ]);

        $label = (int) $request->input('label');
        $files = $request->file('example_images');
        $targetDir = public_path("dataset/examples/{$label}");

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $uploadedCount = 0;
        foreach ($files as $file) {
            $filename = 'example_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($targetDir, $filename);

            // Record in the database
            \App\Models\ExampleImage::create([
                'filename' => $filename,
                'label' => $label
            ]);

            $uploadedCount++;
        }

        return back()->with('success', "Berhasil mengunggah {$uploadedCount} gambar contoh baru untuk label {$label}!");
    }

    /**
     * Handle manual example image deletion.
     */
    public function deleteExample(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);

        $example = \App\Models\ExampleImage::findOrFail($request->input('id'));
        $filePath = public_path("dataset/examples/{$example->label}/{$example->filename}");

        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $example->delete();

        return back()->with('success', "Gambar contoh berhasil dihapus.");
    }

    /**
     * Action to approve a pending label.
     */
    public function approveLabel($id)
    {
        $image = Image::findOrFail($id);
        $image->update(['label_status' => 'approved']);

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "Label approved for image {$image->filename}!");
    }

    /**
     * Action to reject a pending label.
     */
    public function rejectLabel($id)
    {
        $image = Image::findOrFail($id);
        $image->update([
            'label' => null,
            'labeled_by' => null,
            'label_status' => 'unlabeled'
        ]);

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "Label rejected for image {$image->filename}. It is now back in the unlabeled pool.");
    }

    /**
     * Action to edit/update a label and approve it.
     */
    public function updateLabel(Request $request, $id)
    {
        $request->validate([
            'label' => 'required|in:0,1,2'
        ]);

        $image = Image::findOrFail($id);
        $image->update([
            'label' => $request->input('label'),
            'label_status' => 'approved' // Automatically approve when edited by admin
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "Label updated and approved for image {$image->filename}!");
    }

    /**
     * Export labeled dataset to CSV.
     */
    public function downloadCsv()
    {
        // Get all approved labels
        $approvedImages = Image::where('label_status', 'approved')
            ->orderBy('filename', 'asc')
            ->get();

        $csvFileName = 'labeled_dataset_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=" . $csvFileName,
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($approvedImages) {
            $file = fopen('php://output', 'w');
            
            // CSV columns: id, label
            fputcsv($file, ['id', 'label']);

            foreach ($approvedImages as $image) {
                // Extract filename without extension (e.g., "1.jpg" becomes "1")
                $id = pathinfo($image->filename, PATHINFO_FILENAME);
                fputcsv($file, [$id, $image->label]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Approve all pending labels globally or filtered by user/search.
     */
    public function approveAll(Request $request)
    {
        $labeledBy = $request->input('labeled_by');
        $search = $request->input('search');

        $query = Image::where('label_status', 'pending');

        if ($labeledBy) {
            $query->where('labeled_by', $labeledBy);
        }
        if ($search) {
            $cleanSearch = str_ireplace(['.jpg', '.jpeg', '.png', '.webp'], '', $search);
            $query->where(function($q) use ($search, $cleanSearch) {
                $q->where('filename', 'like', '%' . $search . '%')
                  ->orWhere('filename', 'like', '%' . $cleanSearch . '%');
            });
        }

        $approvedCount = $query->update(['label_status' => 'approved']);

        $message = $labeledBy 
            ? "Berhasil menyetujui {$approvedCount} label dari {$labeledBy} secara massal!"
            : "Berhasil menyetujui {$approvedCount} seluruh label pending secara massal!";

        return back()->with('success', $message);
    }

    /**
     * Reject all approved labels (mass reverse to unlabeled pool) globally or filtered by user/search.
     */
    public function rejectAll(Request $request)
    {
        $labeledBy = $request->input('labeled_by');
        $search = $request->input('search');

        $query = Image::where('label_status', 'approved');

        if ($labeledBy) {
            $query->where('labeled_by', $labeledBy);
        }
        if ($search) {
            $cleanSearch = str_ireplace(['.jpg', '.jpeg', '.png', '.webp'], '', $search);
            $query->where(function($q) use ($search, $cleanSearch) {
                $q->where('filename', 'like', '%' . $search . '%')
                  ->orWhere('filename', 'like', '%' . $cleanSearch . '%');
            });
        }

        $rejectedCount = $query->update([
            'label' => null,
            'labeled_by' => null,
            'prodi' => null,
            'label_status' => 'unlabeled'
        ]);

        $message = $labeledBy 
            ? "Berhasil membatalkan persetujuan {$rejectedCount} label dari {$labeledBy} secara massal!"
            : "Berhasil membatalkan persetujuan {$rejectedCount} seluruh label secara massal!";

        return back()->with('success', $message);
    }
}
