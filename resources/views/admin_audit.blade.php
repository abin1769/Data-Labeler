<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Labeler - Admin Audit Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.08), transparent 50%),
                        radial-gradient(circle at bottom left, rgba(239, 68, 68, 0.05), transparent 50%),
                        #0b0f19;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .glass-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .glass-header {
            background: rgba(11, 15, 25, 0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .glow-blue { box-shadow: 0 0 20px rgba(99, 102, 241, 0.15); }
        .glow-red { box-shadow: 0 0 20px rgba(239, 68, 68, 0.15); }
        .glow-amber { box-shadow: 0 0 20px rgba(245, 158, 11, 0.15); }
        .glow-emerald { box-shadow: 0 0 20px rgba(16, 185, 129, 0.15); }
    </style>
</head>
<body class="min-h-screen text-slate-100 flex flex-col relative pb-12">

    <!-- Glowing background lights -->
    <div class="absolute top-20 left-1/4 w-[600px] h-[600px] bg-indigo-600/5 rounded-full blur-[160px] pointer-events-none"></div>
    <div class="absolute bottom-20 right-1/4 w-[600px] h-[600px] bg-red-500/5 rounded-full blur-[160px] pointer-events-none"></div>

    <header class="glass-header sticky top-0 z-50 py-4.5 px-6 md:px-12 flex justify-between items-center">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-red-500 via-pink-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-red-500/25">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div>
                <span class="font-outfit font-black text-xl tracking-tight bg-gradient-to-r from-slate-100 via-slate-200 to-indigo-200 bg-clip-text text-transparent">Data Labeler</span>
                <span class="text-[10px] uppercase font-extrabold tracking-widest text-red-400 bg-red-400/10 px-2 py-0.5 rounded-md ml-2 border border-red-500/20">Admin Audit</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('admin') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-200 transition-colors flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Dashboard Utama
            </a>
            <a href="{{ route('audit.index') }}" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold text-amber-400 hover:bg-amber-400 hover:text-slate-950 transition-all duration-300">
                Mulai Audit &rarr;
            </a>
        </div>
    </header>

    <main class="flex-grow max-w-6xl w-full mx-auto px-4 md:px-8 py-8 space-y-8 z-10">

        <!-- Alert Notification -->
        @if(session('success'))
            <div class="p-4.5 bg-emerald-500/10 border border-emerald-500/25 rounded-2xl text-emerald-400 text-sm font-semibold flex items-center gap-2.5 animate-fade-in shadow-lg shadow-emerald-500/5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4.5 bg-red-500/10 border border-red-500/25 rounded-2xl text-red-400 text-sm font-semibold flex items-center gap-2.5 animate-fade-in shadow-lg shadow-red-500/5">
                <span class="w-2 h-2 rounded-full bg-red-400"></span>
                {{ session('error') }}
            </div>
        @endif

        <!-- Roadmap & Workflow (Visual Friendly Guide) -->
        <section class="glass-card rounded-3xl p-6 md:p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full blur-2xl pointer-events-none"></div>
            
            <h2 class="text-base font-extrabold font-outfit text-slate-100 uppercase tracking-widest mb-6 flex items-center gap-2">
                <span class="w-1.5 h-4 bg-indigo-500 rounded-md"></span>
                Panduan Alur Audit (Roadmap)
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
                <!-- Step 1 -->
                <div class="flex flex-col relative z-10">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-8 h-8 rounded-full bg-indigo-500/20 text-indigo-400 border border-indigo-500/35 flex items-center justify-center font-bold font-outfit">1</span>
                        <h4 class="font-bold text-xs text-slate-200 uppercase tracking-wide">Jalankan Python</h4>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed mb-2">Jalankan analisis data di terminal lokal untuk mengekstrak anomali:</p>
                    <code class="bg-slate-950/80 border border-slate-800/80 p-2 rounded-lg text-[10px] text-indigo-300 font-mono block overflow-x-auto">python scripts/cluster_train_data.py</code>
                </div>

                <!-- Step 2 -->
                <div class="flex flex-col relative z-10">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-8 h-8 rounded-full bg-pink-500/20 text-pink-400 border border-pink-500/35 flex items-center justify-center font-bold font-outfit">2</span>
                        <h4 class="font-bold text-xs text-slate-200 uppercase tracking-wide">Upload ZIP Gambar</h4>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">Unggah file ZIP folder <code class="bg-slate-900 px-1 py-0.5 rounded text-pink-400">train/</code> di panel kanan agar gambar sampah dapat ditampilkan di web.</p>
                </div>

                <!-- Step 3 -->
                <div class="flex flex-col relative z-10">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/35 flex items-center justify-center font-bold font-outfit">3</span>
                        <h4 class="font-bold text-xs text-slate-200 uppercase tracking-wide">Import Hasil CSV</h4>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">Unggah file hasil analisis <code class="bg-slate-900 px-1 py-0.5 rounded text-amber-400">train_clustering_analysis.csv</code> di panel kiri untuk mendata target review.</p>
                </div>

                <!-- Step 4 -->
                <div class="flex flex-col relative z-10">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/35 flex items-center justify-center font-bold font-outfit">4</span>
                        <h4 class="font-bold text-xs text-slate-200 uppercase tracking-wide">Review & Unduh</h4>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">Tim melakukan verifikasi visual. Selesai audit, unduh hasilnya di bagian bawah untuk dieksekusi balik di Python.</p>
                </div>
            </div>
        </section>

        <!-- Statistik Utama (Friendly Cards) -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Card 1 -->
            <div class="glass-card rounded-2xl p-5 glow-blue flex items-center gap-4 relative overflow-hidden group">
                <div class="p-3.5 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/15 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.58 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.58 4 8 4s8-1.79 8-4M4 7c0-2.21 3.58-4 8-4s8 1.79 8 4m0 5c0 2.21-3.58 4-8 4s-8-1.79-8-4" /></svg>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Kandidat</span>
                    <span class="text-2xl font-extrabold font-outfit text-slate-100 mt-1 block">{{ number_format($stats['total'], 0, ',', '.') }}</span>
                </div>
            </div>
            
            <!-- Card 2 -->
            <div class="glass-card rounded-2xl p-5 glow-amber flex items-center gap-4 relative overflow-hidden group">
                <div class="p-3.5 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/15 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">P1: Belum Ditinjau</span>
                    <span class="text-2xl font-extrabold font-outfit text-amber-300 mt-1 block">{{ number_format($stats['round1_pending'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="glass-card rounded-2xl p-5 glow-red flex items-center gap-4 relative overflow-hidden group">
                <div class="p-3.5 rounded-xl bg-red-500/10 text-red-400 border border-red-500/15 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">P2: Belum Direlabel</span>
                    <span class="text-2xl font-extrabold font-outfit text-red-300 mt-1 block">{{ number_format($stats['round2_pending'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="glass-card rounded-2xl p-5 glow-emerald flex items-center gap-4 relative overflow-hidden group">
                <div class="p-3.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/15 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">P2: Selesai</span>
                    <span class="text-2xl font-extrabold font-outfit text-emerald-300 mt-1 block">{{ number_format($stats['round2_done'], 0, ',', '.') }}</span>
                </div>
            </div>
        </section>

        <!-- Breakdown Keputusan Putaran 1 (Status Visual) -->
        <section class="glass-card rounded-3xl p-6 relative">
            <h3 class="text-sm font-extrabold font-outfit text-slate-200 uppercase tracking-wider mb-4 flex items-center gap-2">
                <span class="w-1 h-3.5 bg-indigo-500 rounded"></span>
                Rangkuman Keputusan Putaran 1
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- A -->
                <div class="bg-slate-900/50 border border-slate-800/80 rounded-2xl p-4 flex items-center justify-between">
                    <div>
                        <span class="text-2xl font-black font-outfit text-amber-400 block">{{ $stats['round1_by_decision']['A'] ?? 0 }}</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5 block">Salah Label (A)</span>
                    </div>
                    <span class="text-xs bg-amber-400/10 border border-amber-400/20 text-amber-400 px-2 py-1 rounded-lg font-bold">Pindah Kelas</span>
                </div>

                <!-- B -->
                <div class="bg-slate-900/50 border border-slate-800/80 rounded-2xl p-4 flex items-center justify-between">
                    <div>
                        <span class="text-2xl font-black font-outfit text-red-400 block">{{ $stats['round1_by_decision']['B'] ?? 0 }}</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5 block">Kontaminasi (B)</span>
                    </div>
                    <span class="text-xs bg-red-400/10 border border-red-400/20 text-red-400 px-2 py-1 rounded-lg font-bold">Hapus Data</span>
                </div>

                <!-- C -->
                <div class="bg-slate-900/50 border border-slate-800/80 rounded-2xl p-4 flex items-center justify-between">
                    <div>
                        <span class="text-2xl font-black font-outfit text-purple-400 block">{{ $stats['round1_by_decision']['C'] ?? 0 }}</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5 block">Ambigu (C)</span>
                    </div>
                    <span class="text-xs bg-purple-400/10 border border-purple-400/20 text-purple-400 px-2 py-1 rounded-lg font-bold">Biarkan Asli</span>
                </div>

                <!-- D -->
                <div class="bg-slate-900/50 border border-slate-800/80 rounded-2xl p-4 flex items-center justify-between">
                    <div>
                        <span class="text-2xl font-black font-outfit text-indigo-400 block">{{ $stats['round1_by_decision']['D'] ?? 0 }}</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5 block">Model Error (D)</span>
                    </div>
                    <span class="text-xs bg-indigo-400/10 border border-indigo-400/20 text-indigo-400 px-2 py-1 rounded-lg font-bold">Sudah Benar</span>
                </div>
            </div>
        </section>

        <!-- Dropzone Form Uploads (Stunning & Friendly) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Upload CSV -->
            <div class="glass-card rounded-3xl p-6 md:p-8 flex flex-col justify-between shadow-xl relative">
                <div>
                    <h3 class="text-lg font-extrabold font-outfit text-slate-100 mb-1 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Upload CSV Kandidat
                    </h3>
                    <p class="text-xs text-slate-400 mb-6">Unggah file <code class="bg-slate-900 px-1.5 py-0.5 rounded text-indigo-300">train_clustering_analysis.csv</code> dari script clustering.</p>
                </div>
                <form action="{{ route('admin.audit.upload-csv') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="relative group border-2 border-dashed border-slate-700 hover:border-indigo-500/60 bg-slate-950/20 rounded-2xl p-6 transition-all duration-300 text-center">
                        <input type="file" name="candidates_csv" accept=".csv" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="updateFileName(this, 'csv-file-name')">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-indigo-500/10 text-indigo-400 flex items-center justify-center border border-indigo-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            </div>
                            <span id="csv-file-name" class="text-xs font-bold text-slate-300">Klik / Seret berkas CSV ke sini</span>
                            <span class="text-[10px] text-slate-500">Mendukung format .csv atau .txt</span>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/10 active:scale-[0.98] transition-all">Import CSV & Urutkan Prioritas</button>
                </form>
            </div>

            <!-- Upload ZIP -->
            <div class="glass-card rounded-3xl p-6 md:p-8 flex flex-col justify-between shadow-xl relative">
                <div>
                    <h3 class="text-lg font-extrabold font-outfit text-slate-100 mb-1 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                        Upload ZIP Gambar Train
                    </h3>
                    <p class="text-xs text-slate-400 mb-6">Unggah folder gambar <code class="bg-slate-900 px-1.5 py-0.5 rounded text-pink-400">train/</code> agar sistem Laravel bisa memuat gambar asli di browser.</p>
                </div>
                <form action="{{ route('admin.audit.upload-train-zip') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="relative group border-2 border-dashed border-slate-700 hover:border-pink-500/60 bg-slate-950/20 rounded-2xl p-6 transition-all duration-300 text-center">
                        <input type="file" name="train_zip" accept=".zip" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="updateFileName(this, 'zip-file-name')">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-pink-500/10 text-pink-400 flex items-center justify-center border border-pink-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            </div>
                            <span id="zip-file-name" class="text-xs font-bold text-slate-300">Klik / Seret berkas ZIP ke sini</span>
                            <span class="text-[10px] text-slate-500">ZIP wajib berisi folder 0_Recyclable, dst.</span>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-3.5 px-4 bg-pink-600 hover:bg-pink-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-pink-600/10 active:scale-[0.98] transition-all">Ekstrak ZIP Gambar</button>
                </form>
            </div>
        </div>

        <!-- Download Section (Friendly Action Card) -->
        <section class="glass-card rounded-3xl p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                </div>
                <div>
                    <h3 class="text-base font-extrabold font-outfit text-slate-100">Unduh Hasil Pembersihan</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-xl leading-relaxed">Ekspor berkas CSV keputusan audit yang sudah diselaraskan format kolomnya agar langsung kompatibel untuk dieksekusi balik oleh skrip Python lokal Anda.</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3.5 w-full md:w-auto shrink-0">
                <a href="{{ route('admin.audit.download-round1') }}" class="w-full sm:w-auto text-center px-6 py-3.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-amber-600/10 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200">
                    Unduh Putaran 1 (label_review.csv)
                </a>
                <a href="{{ route('admin.audit.download-round2') }}" class="w-full sm:w-auto text-center px-6 py-3.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-red-600/10 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200">
                    Unduh Putaran 2 (relabel_review.csv)
                </a>
            </div>
        </section>
    </main>

    <footer class="text-center pb-8 mt-4 text-[11px] text-slate-500 font-medium tracking-wide z-10">&copy; 2026 &bull; Tim BDC Satria Data Universitas Jambi</footer>

    <script>
        function updateFileName(input, targetId) {
            const label = document.getElementById(targetId);
            if (input.files && input.files.length > 0) {
                const name = input.files[0].name;
                const size = (input.files[0].size / (1024 * 1024)).toFixed(2);
                label.innerHTML = `<span class="text-indigo-400 font-black">${name}</span> <span class="text-[10px] text-slate-400 font-normal">(${size} MB)</span>`;
            } else {
                label.textContent = "Klik / Seret berkas ke sini";
            }
        }
    </script>
</body>
</html>
