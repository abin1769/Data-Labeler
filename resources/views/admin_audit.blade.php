<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Labeler - Admin Audit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'], outfit: ['Outfit', 'sans-serif'] } } } }
    </script>
    <style>
        body {
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.05), transparent 45%),
                        radial-gradient(circle at bottom left, rgba(168, 85, 247, 0.05), transparent 45%),
                        #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .glass-card { background: rgba(30, 41, 59, 0.55); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.06); }
        .glass-header { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="min-h-screen text-slate-100 flex flex-col relative pb-8">

    <div class="absolute top-10 left-10 w-[500px] h-[500px] bg-red-500/5 rounded-full blur-[150px] pointer-events-none"></div>
    <div class="absolute bottom-10 right-10 w-[500px] h-[500px] bg-indigo-500/5 rounded-full blur-[150px] pointer-events-none"></div>

    <header class="glass-header sticky top-0 z-50 py-4 px-6 md:px-12 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-red-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-red-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <span class="font-outfit font-bold text-lg tracking-tight bg-gradient-to-r from-slate-200 to-red-200 bg-clip-text text-transparent">Data Labeler</span>
                <span class="text-[10px] uppercase font-bold tracking-widest text-red-400 bg-red-400/10 px-1.5 py-0.5 rounded ml-2">Admin -- Audit Label</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('admin') }}" class="text-xs font-semibold text-slate-300 hover:text-indigo-400 transition-colors duration-200">&larr; Admin Panel Utama</a>
            <a href="{{ route('audit.index') }}" class="text-xs font-semibold text-slate-300 hover:text-amber-400 transition-colors duration-200">Lihat Putaran 1</a>
        </div>
    </header>

    <main class="flex-grow max-w-6xl w-full mx-auto px-4 md:px-8 py-8 space-y-8 z-10">

        @if(session('success'))
            <div class="p-4 bg-emerald-500/15 border border-emerald-500/30 rounded-2xl text-emerald-400 text-sm font-semibold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-red-500/15 border border-red-500/30 rounded-2xl text-red-400 text-sm font-semibold">{{ session('error') }}</div>
        @endif

        <p class="text-xs text-slate-400 glass-card rounded-2xl p-4">
            Alur: (1) jalankan <code class="bg-slate-900 px-1.5 py-0.5 rounded text-indigo-300">scripts/audit_data.py</code> di project Python untuk hasilkan <code class="bg-slate-900 px-1.5 py-0.5 rounded text-indigo-300">label_issues.csv</code>,
            (2) upload ZIP folder <code class="bg-slate-900 px-1.5 py-0.5 rounded text-indigo-300">train/</code> supaya gambar bisa ditampilkan, (3) upload CSV-nya di sini,
            (4) tim tinjau di <a href="{{ route('audit.index') }}" class="text-amber-400 underline">Putaran 1</a> lalu <a href="{{ route('audit.relabel') }}" class="text-red-400 underline">Putaran 2</a>,
            (5) unduh hasilnya di bawah untuk dieksekusi balik lewat <code class="bg-slate-900 px-1.5 py-0.5 rounded text-indigo-300">scripts/remove_contamination.py</code> / <code class="bg-slate-900 px-1.5 py-0.5 rounded text-indigo-300">scripts/execute_relabel.py</code>.
        </p>

        <!-- Statistik -->
        <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="glass-card rounded-2xl p-5">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Kandidat</span>
                <span class="text-3xl font-extrabold font-outfit text-slate-100 mt-2 block">{{ number_format($stats['total'], 0, ',', '.') }}</span>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Putaran 1: Belum Ditinjau</span>
                <span class="text-3xl font-extrabold font-outfit text-amber-300 mt-2 block">{{ number_format($stats['round1_pending'], 0, ',', '.') }}</span>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Putaran 2: Belum Direlabel</span>
                <span class="text-3xl font-extrabold font-outfit text-red-300 mt-2 block">{{ number_format($stats['round2_pending'], 0, ',', '.') }}</span>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Putaran 2: Selesai</span>
                <span class="text-3xl font-extrabold font-outfit text-emerald-300 mt-2 block">{{ number_format($stats['round2_done'], 0, ',', '.') }}</span>
            </div>
        </section>

        <!-- Breakdown Putaran 1 -->
        <section class="glass-card rounded-2xl p-5">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-3">Breakdown Keputusan Putaran 1</span>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach(['A' => 'Salah label', 'B' => 'Kontaminasi', 'C' => 'Ambigu', 'D' => 'Model error'] as $code => $label)
                    <div class="bg-slate-900/50 rounded-xl p-3 text-center">
                        <span class="block text-2xl font-bold font-outfit text-slate-100">{{ $stats['round1_by_decision'][$code] ?? 0 }}</span>
                        <span class="block text-[10px] text-slate-400 uppercase tracking-wide">{{ $code }} -- {{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Upload -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="glass-card rounded-3xl p-6 relative shadow-xl">
                <h3 class="text-lg font-bold font-outfit text-slate-100 mb-1">Upload CSV Kandidat</h3>
                <p class="text-xs text-slate-400 mb-4">Upload <code class="bg-slate-900 px-1.5 py-0.5 rounded text-indigo-300">label_issues.csv</code> dari <code class="bg-slate-900 px-1.5 py-0.5 rounded text-indigo-300">scripts/audit_data.py</code>. Aman diupload ulang -- tidak menimpa keputusan yang sudah ada.</p>
                <form action="{{ route('admin.audit.upload-csv') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="file" name="candidates_csv" accept=".csv" required class="w-full bg-slate-900 border border-slate-700/60 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-lg transition-all">Upload & Import</button>
                </form>
            </div>

            <div class="glass-card rounded-3xl p-6 relative shadow-xl">
                <h3 class="text-lg font-bold font-outfit text-slate-100 mb-1">Upload ZIP Gambar Train</h3>
                <p class="text-xs text-slate-400 mb-4">ZIP berisi struktur folder <code class="bg-slate-900 px-1.5 py-0.5 rounded text-indigo-300">0_Recyclable/</code>, <code class="bg-slate-900 px-1.5 py-0.5 rounded text-indigo-300">1_Electronic/</code>, <code class="bg-slate-900 px-1.5 py-0.5 rounded text-indigo-300">2_Organic/</code> -- diekstrak ke <code class="bg-slate-900 px-1.5 py-0.5 rounded text-indigo-300">dataset/train/</code>.</p>
                <form action="{{ route('admin.audit.upload-train-zip') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="file" name="train_zip" accept=".zip" required class="w-full bg-slate-900 border border-slate-700/60 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-lg transition-all">Upload & Ekstrak</button>
                </form>
            </div>
        </div>

        <!-- Download -->
        <div class="glass-card rounded-3xl p-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold font-outfit text-slate-100">Unduh Hasil</h3>
                <p class="text-xs text-slate-400 mt-1">Format kolomnya sudah persis sama dengan CSV dari tool Python -- langsung bisa dipakai skrip eksekusi tanpa diolah lagi.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.audit.download-round1') }}" class="px-5 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold shadow-lg transition-all">Unduh Putaran 1 (label_review.csv)</a>
                <a href="{{ route('admin.audit.download-round2') }}" class="px-5 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-semibold shadow-lg transition-all">Unduh Putaran 2 (relabel_review.csv)</a>
            </div>
        </div>
    </main>

    <footer class="text-center pb-8 mt-4 text-[11px] text-slate-500 font-medium tracking-wide z-10">&copy; 2026 &bull; Tim BDC Satria Data Universitas Jambi</footer>
</body>
</html>
