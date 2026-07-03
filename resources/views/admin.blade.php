<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Labeler - Admin Dashboard</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <!-- Axios for API requests -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        body {
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.04), transparent 45%),
                        radial-gradient(circle at bottom left, rgba(220, 38, 38, 0.03), transparent 45%),
                        #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.55);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        .glass-header {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        /* Fade out row animation */
        .row-fade-out {
            transition: all 0.4s ease-out;
            opacity: 0;
            transform: translateX(-20px);
            height: 0;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            border: none !important;
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 flex flex-col relative pb-12">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Glowing Background Orbs -->
    <div class="absolute top-10 right-10 w-[500px] h-[500px] bg-red-500/3 rounded-full blur-[150px] pointer-events-none"></div>
    <div class="absolute bottom-10 left-10 w-[500px] h-[500px] bg-indigo-500/3 rounded-full blur-[150px] pointer-events-none"></div>

    <!-- Header -->
    <header class="glass-header sticky top-0 z-50 py-4 px-6 md:px-12 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-red-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-red-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <div>
                <span class="font-outfit font-bold text-lg tracking-tight bg-gradient-to-r from-slate-200 to-red-200 bg-clip-text text-transparent">Data Labeler</span>
                <span class="text-[10px] uppercase font-bold tracking-widest text-red-400 bg-red-400/10 px-1.5 py-0.5 rounded ml-2">Admin Panel</span>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-300 hover:text-indigo-400 transition-colors duration-200">
                Workspace Label
            </a>
            
            <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 hover:border-red-500/40 text-red-400 rounded-xl text-xs font-semibold transition-all duration-200">
                    Keluar Admin
                </button>
            </form>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 md:px-8 py-8 space-y-8 z-10">

        <!-- Status Alerts -->
        @if(session('success'))
            <div class="p-4 bg-emerald-500/15 border border-emerald-500/30 rounded-2xl text-emerald-400 text-sm font-semibold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="p-4 bg-amber-500/15 border border-amber-500/30 rounded-2xl text-amber-400 text-sm font-semibold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                {{ session('warning') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-red-500/15 border border-red-500/30 rounded-2xl text-red-400 text-sm font-semibold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <!-- Synchronization & Download Toolbar -->
        <div class="glass-card rounded-3xl p-6 flex flex-col md:flex-row items-center justify-between gap-6 shadow-xl relative">
            <div class="absolute -top-px left-8 right-8 h-px bg-gradient-to-r from-transparent via-slate-500/20 to-transparent"></div>
            <div>
                <h2 class="text-xl font-bold font-outfit text-slate-100">Manajemen Dataset & Hasil</h2>
                <p class="text-xs text-slate-400 mt-1">Gunakan tombol sync untuk memindai folder lokal <code class="bg-slate-900 px-1.5 py-0.5 rounded text-indigo-300">D:\SATRIA DATA\test</code>, dan tombol unduh untuk mendapatkan file CSV akhir.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <form action="{{ route('admin.sync') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="w-full px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-lg shadow-indigo-600/15 hover:shadow-indigo-600/30 transform hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2" />
                        </svg>
                        Sinkronisasi Folder
                    </button>
                </form>
                
                <a href="{{ route('admin.download') }}" class="w-full sm:w-auto px-5 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl text-xs font-semibold shadow-lg shadow-emerald-500/15 hover:shadow-emerald-500/30 transform hover:-translate-y-0.5 active:translate-y-0 transition-all text-center flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Unduh CSV Hasil
                </a>
            </div>
        </div>
        <!-- Upload Panel Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Upload Dataset ZIP Card -->
            <div class="glass-card rounded-3xl p-6 relative shadow-xl">
                <div class="absolute -top-px left-8 right-8 h-px bg-gradient-to-r from-transparent via-indigo-500/30 to-transparent"></div>
                <div class="mb-4">
                    <h3 class="text-lg font-bold font-outfit text-slate-100 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Upload ZIP Dataset (Data Test)
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">Unggah file ZIP berisi kumpulan gambar test langsung dari komputer lokal Anda ke server.</p>
                </div>

                <form id="zip-upload-form" onsubmit="uploadZip(event)" class="space-y-4">
                    @csrf
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-700 border-dashed rounded-2xl cursor-pointer bg-slate-900/40 hover:bg-slate-900/60 transition-all duration-200">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mb-2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2-8H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V8l-6-6z" />
                                </svg>
                                <p class="mb-1 text-xs text-slate-300 font-semibold" id="zip-file-label">Klik untuk memilih file ZIP</p>
                                <p class="text-[10px] text-slate-500">ZIP (Maksimal 200MB)</p>
                            </div>
                            <input type="file" name="dataset_zip" id="dataset_zip" accept=".zip" class="hidden" required onchange="updateZipFileName(this)" />
                        </label>
                    </div>

                    <!-- Progress Bar Container -->
                    <div id="upload-progress-container" class="hidden space-y-2 p-3 bg-slate-900/60 rounded-xl border border-slate-800">
                        <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            <span id="upload-progress-text">Mengunggah: 0%</span>
                            <span id="upload-speed-text">-- KB/s</span>
                        </div>
                        <div class="w-full bg-slate-950 rounded-full h-2 overflow-hidden border border-slate-850">
                            <div id="upload-progress-bar" class="bg-indigo-500 h-full rounded-full transition-all duration-150" style="width: 0%"></div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-lg shadow-indigo-600/20 transition-all duration-200 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Ekstrak & Sinkronisasi ZIP
                    </button>
                </form>
            </div>

            <!-- Upload Guidelines Examples Card -->
            <div class="glass-card rounded-3xl p-6 relative shadow-xl">
                <div class="absolute -top-px left-8 right-8 h-px bg-gradient-to-r from-transparent via-purple-500/30 to-transparent"></div>
                <div class="mb-4">
                    <h3 class="text-lg font-bold font-outfit text-slate-100 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Upload Gambar Contoh (Guidelines)
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">Unggah beberapa gambar sampel untuk dijadikan panduan klasifikasi di workspace pelabelan.</p>
                </div>

                <form action="{{ route('admin.upload-examples') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-1">
                            <label for="label_select" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Kelas</label>
                            <select name="label" id="label_select" class="w-full bg-slate-900 border border-slate-700/60 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500 font-medium">
                                <option value="0">0 - Recycleable</option>
                                <option value="1">1 - Electronics</option>
                                <option value="2">2 - Organics</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Pilih File Gambar</label>
                            <div class="relative">
                                <input type="file" name="example_images[]" id="example_images" accept="image/*" multiple required class="w-full bg-slate-900 border border-slate-700/60 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:ring-1 focus:ring-purple-500 font-medium" />
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold shadow-lg shadow-purple-600/20 transition-all duration-200 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Upload Gambar Contoh
                    </button>
                </form>

                <!-- Current Example Images Gallery -->
                <div class="mt-4 pt-4 border-t border-slate-800">
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Daftar Contoh Saat Ini:</h4>
                    <div class="space-y-3 max-h-[140px] overflow-y-auto pr-1">
                        @foreach([0, 1, 2] as $lbl)
                            <div class="flex flex-col gap-1.5">
                                <div class="text-[11px] font-bold text-slate-300 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full {{ $lbl == 0 ? 'bg-emerald-500' : ($lbl == 1 ? 'bg-blue-500' : 'bg-amber-500') }}"></span>
                                    Kelas {{ $lbl }} ({{ $lbl == 0 ? 'Recycleable' : ($lbl == 1 ? 'Electronics' : 'Organics') }})
                                </div>
                                @if(empty($manualExamples[$lbl]))
                                    <div class="text-[10px] text-slate-500 italic pl-3.5">Belum ada contoh manual.</div>
                                @else
                                    <div class="flex flex-wrap gap-2 pl-3.5">
                                        @foreach($manualExamples[$lbl] as $ex)
                                            <div class="relative w-10 h-10 rounded border border-slate-850 bg-slate-900 overflow-hidden group">
                                                <img src="{{ $ex['url'] }}" class="w-full h-full object-cover">
                                                <form action="{{ route('admin.delete-example') }}" method="POST" class="absolute inset-0 bg-slate-950/80 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-150">
                                                    @csrf
                                                    <input type="hidden" name="label" value="{{ $lbl }}">
                                                    <input type="hidden" name="filename" value="{{ $ex['filename'] }}">
                                                    <button type="submit" class="text-red-400 hover:text-red-300" title="Hapus Gambar">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
        <!-- Statistics Dashboard -->
        <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="glass-card rounded-2xl p-5">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Database</span>
                <span class="text-3xl font-extrabold font-outfit text-slate-100 mt-2 block">{{ number_format($stats['total'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 mt-1 block">Seluruh data yang terdaftar</span>
            </div>
            
            <div class="glass-card rounded-2xl p-5">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Belum Dilabel (Unlabeled)</span>
                <span class="text-3xl font-extrabold font-outfit text-indigo-400 mt-2 block">{{ number_format($stats['unlabeled'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 mt-1 block">Tersisa di pool workspace</span>
            </div>

            <div class="glass-card rounded-2xl p-5">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Perlu Validasi (Pending)</span>
                <span class="text-3xl font-extrabold font-outfit text-amber-400 mt-2 block">{{ number_format($stats['pending'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 mt-1 block">Menunggu persetujuan admin</span>
            </div>

            <div class="glass-card rounded-2xl p-5">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Disetujui (Approved)</span>
                <span class="text-3xl font-extrabold font-outfit text-emerald-400 mt-2 block">{{ number_format($stats['approved'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 mt-1 block">Akan diexport ke CSV hasil</span>
            </div>
        </section>

        <!-- Class Distribution Summary (Approved only) -->
        <div class="glass-card rounded-3xl p-6 relative">
            <div class="absolute -top-px left-8 right-8 h-px bg-gradient-to-r from-transparent via-slate-500/10 to-transparent"></div>
            <h3 class="font-outfit font-bold text-sm text-slate-300 uppercase tracking-widest mb-4">Distribusi Kelas Terlabel Disetujui</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="flex items-center gap-3 bg-slate-900/40 border border-slate-800/80 rounded-2xl p-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">0</div>
                    <div>
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Recycleable</span>
                        <span class="text-lg font-bold text-slate-200">{{ number_format($stats['recycleable'], 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">img</span></span>
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-slate-900/40 border border-slate-800/80 rounded-2xl p-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/15 border border-blue-500/20 text-blue-400 flex items-center justify-center font-bold">1</div>
                    <div>
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Electronics</span>
                        <span class="text-lg font-bold text-slate-200">{{ number_format($stats['electronics'], 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">img</span></span>
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-slate-900/40 border border-slate-800/80 rounded-2xl p-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-500/20 text-amber-400 flex items-center justify-center font-bold">2</div>
                    <div>
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Organics</span>
                        <span class="text-lg font-bold text-slate-200">{{ number_format($stats['organics'], 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">img</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Items Table -->
        <section class="glass-card rounded-3xl p-6 md:p-8 relative shadow-2xl">
            <div class="absolute -top-px left-8 right-8 h-px bg-gradient-to-r from-transparent via-amber-500/30 to-transparent"></div>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold font-outfit text-slate-100 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                        Menunggu Validasi ({{ $pendingItems->total() }})
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">Tinjau hasil kerjaan teman-temanmu. Setujui, tolak, atau ubah label langsung.</p>
                </div>

                <!-- Sort, Filter & Mass Approve Toolbar -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Filter & Search Form -->
                    <form action="{{ route('admin') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <!-- Search Box -->
                        <div class="relative">
                            <input type="text" name="search_pending" value="{{ $searchPending }}" placeholder="Cari ID / nama file..." class="bg-slate-900 border border-slate-700/60 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-amber-500 font-medium placeholder-slate-500 w-44">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>

                        <!-- Filter Dropdown -->
                        <select name="filter_user" onchange="this.form.submit()" class="bg-slate-900 border border-slate-700/60 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-amber-500 font-medium">
                            <option value="">Semua Labeler (No Filter)</option>
                            @foreach($pendingLabelers as $labeler)
                                <option value="{{ $labeler }}" {{ $filterUser == $labeler ? 'selected' : '' }}>
                                    Filter: {{ $labeler }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Cari button -->
                        <button type="submit" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold transition">
                            Cari
                        </button>

                        @if($filterUser || $searchPending)
                            <a href="{{ route('admin') }}" class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-slate-200 rounded-xl text-xs font-semibold transition" title="Reset Filter & Pencarian">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        @endif
                    </form>

                    <!-- Approve All Form -->
                    <form action="{{ route('admin.approve-all') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui semua data ini secara massal?')" class="flex items-center">
                        @csrf
                        @if($filterUser)
                            <input type="hidden" name="labeled_by" value="{{ $filterUser }}">
                        @endif
                        @if($searchPending)
                            <input type="hidden" name="search" value="{{ $searchPending }}">
                        @endif
                        <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-lg shadow-emerald-600/20 transform hover:-translate-y-0.5 active:translate-y-0 transition duration-150 flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            @if($filterUser && $searchPending)
                                Setujui Semua Cocok
                            @elseif($filterUser)
                                Setujui Semua dari "{{ $filterUser }}"
                            @elseif($searchPending)
                                Setujui Hasil Pencarian
                            @else
                                Setujui Semua (Global)
                            @endif
                        </button>
                    </form>
                </div>
            </div>

            <!-- Table Container -->
            <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-950/20">
                <table class="w-full border-collapse text-left text-sm text-slate-300">
                    <thead>
                        <tr class="border-b border-slate-800 bg-slate-900/40 text-xs font-bold uppercase tracking-wider text-slate-400">
                            <th class="py-4 px-6 w-28">Preview</th>
                            <th class="py-4 px-6">Nama File</th>
                            <th class="py-4 px-6">Labeler (Prodi)</th>
                            <th class="py-4 px-6">Label Usulan</th>
                            <th class="py-4 px-6 text-center w-72">Aksi Validasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60" id="pending-table-body">
                        @forelse($pendingItems as $item)
                            <tr class="hover:bg-slate-900/20 transition-colors" id="row-pending-{{ $item->id }}">
                                <!-- Image Preview with Hover Zoom -->
                                <td class="py-4 px-6">
                                    <div class="relative group w-16 h-12 rounded-lg bg-slate-900 border border-slate-850 overflow-hidden flex items-center justify-center cursor-zoom-in">
                                        <img 
                                            src="{{ route('images.show', ['filename' => $item->filename]) }}" 
                                            alt="{{ $item->filename }}"
                                            class="h-full w-full object-contain transition-transform duration-200 group-hover:scale-125"
                                        >
                                        <!-- Magnified Hover Overlay -->
                                    </div>
                                </td>
                                <!-- Filename -->
                                <td class="py-4 px-6 font-medium text-slate-200">
                                    {{ $item->filename }}
                                </td>
                                <!-- Labeler (Prodi) -->
                                <td class="py-4 px-6">
                                    <div class="font-semibold text-slate-200">{{ $item->labeled_by }}</div>
                                    <div class="text-[11px] text-slate-400 font-medium">{{ $item->prodi }}</div>
                                </td>
                                <!-- Proposed Label Badge -->
                                <td class="py-4 px-6">
                                    @if($item->label === 0)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            0 - Recycleable
                                        </span>
                                    @elseif($item->label === 1)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                            1 - Electronics
                                        </span>
                                    @elseif($item->label === 2)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            2 - Organics
                                        </span>
                                    @endif
                                </td>
                                <!-- Actions -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Approve proposed -->
                                        <button 
                                            onclick="approveLabel({{ $item->id }})" 
                                            title="Setujui Label"
                                            class="p-2 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 hover:border-emerald-500/40 text-emerald-400 rounded-xl transition-all duration-150 active:scale-95"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>

                                        <!-- Correct label to 0 -->
                                        <button 
                                            onclick="updateLabel({{ $item->id }}, 0)" 
                                            title="Ubah & Setujui ke Recycleable (0)"
                                            class="px-2 py-1 bg-slate-800 hover:bg-emerald-600/20 border border-slate-700 hover:border-emerald-500/30 text-slate-400 hover:text-emerald-400 rounded-lg text-xs font-bold transition-all"
                                        >
                                            Set 0
                                        </button>

                                        <!-- Correct label to 1 -->
                                        <button 
                                            onclick="updateLabel({{ $item->id }}, 1)" 
                                            title="Ubah & Setujui ke Electronics (1)"
                                            class="px-2 py-1 bg-slate-800 hover:bg-blue-600/20 border border-slate-700 hover:border-blue-500/30 text-slate-400 hover:text-blue-400 rounded-lg text-xs font-bold transition-all"
                                        >
                                            Set 1
                                        </button>

                                        <!-- Correct label to 2 -->
                                        <button 
                                            onclick="updateLabel({{ $item->id }}, 2)" 
                                            title="Ubah & Setujui ke Organics (2)"
                                            class="px-2 py-1 bg-slate-800 hover:bg-amber-600/20 border border-slate-700 hover:border-amber-500/30 text-slate-400 hover:text-amber-400 rounded-lg text-xs font-bold transition-all"
                                        >
                                            Set 2
                                        </button>

                                        <!-- Reject (revert to unlabeled) -->
                                        <button 
                                            onclick="rejectLabel({{ $item->id }})" 
                                            title="Tolak Label (Kembalikan ke Pool)"
                                            class="p-2 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 hover:border-red-500/40 text-red-400 rounded-xl transition-all duration-150 active:scale-95 ml-2"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-500 font-medium">
                                    Tidak ada data label yang perlu divalidasi. Pekerjaan teman-temanmu sudah beres!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="mt-6">
                {{ $pendingItems->appends(['approved_page' => $approvedItems->currentPage(), 'filter_user' => $filterUser, 'search_pending' => $searchPending])->links() }}
            </div>

        </section>

        <!-- Approved Items Table (Recently Validated) -->
        <section class="glass-card rounded-3xl p-6 md:p-8 relative shadow-lg">
            <div class="absolute -top-px left-8 right-8 h-px bg-gradient-to-r from-transparent via-emerald-500/20 to-transparent"></div>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold font-outfit text-slate-100 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        Sudah Divalidasi / Disetujui ({{ $approvedItems->total() }})
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">Daftar label yang disetujui. Kamu bisa merevisi label jika ada kekeliruan.</p>
                </div>

                <!-- Sort, Filter & Mass Revert Toolbar -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Filter & Search Form -->
                    <form action="{{ route('admin') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <!-- Search Box -->
                        <div class="relative">
                            <input type="text" name="search_approved" value="{{ $searchApproved }}" placeholder="Cari ID / nama file..." class="bg-slate-900 border border-slate-700/60 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-medium placeholder-slate-500 w-44">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>

                        <!-- Filter Dropdown -->
                        <select name="filter_approved_user" onchange="this.form.submit()" class="bg-slate-900 border border-slate-700/60 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-medium">
                            <option value="">Semua Labeler (No Filter)</option>
                            @foreach($approvedLabelers as $labeler)
                                <option value="{{ $labeler }}" {{ $filterApprovedUser == $labeler ? 'selected' : '' }}>
                                    Filter: {{ $labeler }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Cari button -->
                        <button type="submit" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold transition">
                            Cari
                        </button>

                        @if($filterApprovedUser || $searchApproved)
                            <a href="{{ route('admin') }}" class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-slate-200 rounded-xl text-xs font-semibold transition" title="Reset Filter & Pencarian">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        @endif
                    </form>

                    <!-- Reject All Form (Mass Revert to Pool) -->
                    <form action="{{ route('admin.reject-all') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan semua persetujuan data ini secara massal? Semuanya akan kembali ke pool belum terlabel.')" class="flex items-center">
                        @csrf
                        @if($filterApprovedUser)
                            <input type="hidden" name="labeled_by" value="{{ $filterApprovedUser }}">
                        @endif
                        @if($searchApproved)
                            <input type="hidden" name="search" value="{{ $searchApproved }}">
                        @endif
                        <button type="submit" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-semibold shadow-lg shadow-red-600/20 transform hover:-translate-y-0.5 active:translate-y-0 transition duration-150 flex items-center gap-1.5" title="Kembalikan semua ke unlabeled pool">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2" />
                            </svg>
                            @if($filterApprovedUser && $searchApproved)
                                Batalkan Semua Cocok
                            @elseif($filterApprovedUser)
                                Batalkan Semua dari "{{ $filterApprovedUser }}"
                            @elseif($searchApproved)
                                Batalkan Hasil Pencarian
                            @else
                                Batalkan Semua (Global)
                            @endif
                        </button>
                    </form>
                </div>
            </div>

            <!-- Table Container -->
            <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-950/10">
                <table class="w-full border-collapse text-left text-sm text-slate-400">
                    <thead>
                        <tr class="border-b border-slate-800 bg-slate-900/20 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            <th class="py-4 px-6 w-24">Preview</th>
                            <th class="py-4 px-6">Nama File</th>
                            <th class="py-4 px-6">Labeler (Prodi)</th>
                            <th class="py-4 px-6">Label Disetujui</th>
                            <th class="py-4 px-6 text-center w-60">Ubah Kembali</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/40" id="approved-table-body">
                        @forelse($approvedItems as $item)
                            <tr class="hover:bg-slate-900/10 transition-colors" id="row-approved-{{ $item->id }}">
                                <td class="py-3 px-6">
                                    <div class="w-14 h-10 rounded bg-slate-900 border border-slate-850 overflow-hidden flex items-center justify-center">
                                        <img 
                                            src="{{ route('images.show', ['filename' => $item->filename]) }}" 
                                            alt="{{ $item->filename }}"
                                            class="h-full w-full object-contain"
                                        >
                                    </div>
                                </td>
                                <td class="py-3 px-6 font-medium text-slate-300">
                                    {{ $item->filename }}
                                </td>
                                <td class="py-3 px-6">
                                    <div class="font-medium text-slate-300 text-xs">{{ $item->labeled_by }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $item->prodi }}</div>
                                </td>
                                <td class="py-3 px-6 font-bold text-slate-200">
                                    @if($item->label === 0)
                                        <span class="text-emerald-400">0 - Recycleable</span>
                                    @elseif($item->label === 1)
                                        <span class="text-blue-400">1 - Electronics</span>
                                    @elseif($item->label === 2)
                                        <span class="text-amber-400">2 - Organics</span>
                                    @endif
                                </td>
                                <td class="py-3 px-6">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button onclick="updateLabel({{ $item->id }}, 0, true)" class="px-2 py-0.5 text-[10px] bg-slate-800 border border-slate-700 hover:border-emerald-500/35 hover:text-emerald-400 rounded font-semibold transition-all">Set 0</button>
                                        <button onclick="updateLabel({{ $item->id }}, 1, true)" class="px-2 py-0.5 text-[10px] bg-slate-800 border border-slate-700 hover:border-blue-500/35 hover:text-blue-400 rounded font-semibold transition-all">Set 1</button>
                                        <button onclick="updateLabel({{ $item->id }}, 2, true)" class="px-2 py-0.5 text-[10px] bg-slate-800 border border-slate-700 hover:border-amber-500/35 hover:text-amber-400 rounded font-semibold transition-all">Set 2</button>
                                        <button onclick="rejectLabel({{ $item->id }}, true)" title="Batalkan Persetujuan (Balik ke Pool)" class="p-1 bg-red-500/10 hover:bg-red-500/25 border border-red-500/20 text-red-400 rounded ml-1.5 transition-all"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-600 text-xs">
                                    Belum ada data label yang disetujui.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="mt-6">
                {{ $approvedItems->appends(['pending_page' => $pendingItems->currentPage(), 'filter_approved_user' => $filterApprovedUser, 'search_approved' => $searchApproved])->links() }}
            </div>
        </section>

    </main>

    <!-- AJAX Interactive actions scripting -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

        // Approve Label Action
        function approveLabel(id) {
            axios.post(`/admin/approve/${id}`)
                .then(response => {
                    const row = document.getElementById(`row-pending-${id}`);
                    if (row) {
                        row.classList.add('row-fade-out');
                        setTimeout(() => row.remove(), 400);
                        refreshStatsQuietly();
                    }
                })
                .catch(error => {
                    console.error('Error approving label:', error);
                    alert('Gagal menyetujui label. Silakan coba lagi.');
                });
        }

        // Reject Label Action
        function rejectLabel(id, fromApproved = false) {
            axios.post(`/admin/reject/${id}`)
                .then(response => {
                    const prefix = fromApproved ? 'row-approved-' : 'row-pending-';
                    const row = document.getElementById(`${prefix}${id}`);
                    if (row) {
                        row.classList.add('row-fade-out');
                        setTimeout(() => row.remove(), 400);
                        refreshStatsQuietly();
                    }
                })
                .catch(error => {
                    console.error('Error rejecting label:', error);
                    alert('Gagal menolak label. Silakan coba lagi.');
                });
        }

        // Update Label to custom value Action
        function updateLabel(id, labelValue, fromApproved = false) {
            axios.post(`/admin/update/${id}`, {
                label: labelValue
            })
            .then(response => {
                // If it is from the pending list, it gets approved, so we remove from pending list
                const prefix = fromApproved ? 'row-approved-' : 'row-pending-';
                const row = document.getElementById(`${prefix}${id}`);
                if (row) {
                    if (!fromApproved) {
                        row.classList.add('row-fade-out');
                        setTimeout(() => row.remove(), 400);
                    } else {
                        // Just reload page to show correct state in approved if editing already approved
                        window.location.reload();
                        return;
                    }
                    refreshStatsQuietly();
                }
            })
            .catch(error => {
                console.error('Error updating label:', error);
                alert('Gagal merubah label. Silakan coba lagi.');
            });
        }

        // Helper: refresh statistics values without full page reload
        function refreshStatsQuietly() {
            // We can reload the page or do a quiet reload if we want, but since this is administrative,
            // we can just let them refresh or update simple DOM elements. Let's do a quiet reload after a short delay
            // if multiple items are processed, or let the user refresh. Actually, a simple page refresh is fine
            // but page refresh keeps them at the correct scroll position. To make it extremely clean, let's keep it dynamic.
        }

        // Helper to update ZIP file name in input label
        function updateZipFileName(input) {
            const label = document.getElementById('zip-file-label');
            if (input.files && input.files.length > 0) {
                label.textContent = "Terpilih: " + input.files[0].name;
                label.classList.remove('text-slate-300');
                label.classList.add('text-indigo-400');
            } else {
                label.textContent = "Klik untuk memilih file ZIP";
                label.classList.remove('text-indigo-400');
                label.classList.add('text-slate-300');
            }
        }

        // AJAX ZIP Upload with Progress Bar
        function uploadZip(event) {
            event.preventDefault();
            const fileInput = document.getElementById('dataset_zip');
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('Silakan pilih file ZIP terlebih dahulu.');
                return;
            }

            const formData = new FormData();
            formData.append('dataset_zip', fileInput.files[0]);

            const container = document.getElementById('upload-progress-container');
            const bar = document.getElementById('upload-progress-bar');
            const text = document.getElementById('upload-progress-text');
            const speedText = document.getElementById('upload-speed-text');
            const submitBtn = document.querySelector('#zip-upload-form button[type="submit"]');

            container.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

            let startTime = Date.now();

            axios.post("{{ route('admin.upload-dataset') }}", formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                },
                onUploadProgress: function(progressEvent) {
                    if (progressEvent.lengthComputable) {
                        const percentComplete = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        bar.style.width = percentComplete + '%';
                        text.textContent = `Mengunggah: ${percentComplete}%`;
                        
                        const duration = (Date.now() - startTime) / 1000;
                        if (duration > 0) {
                            const speed = progressEvent.loaded / duration; // bytes per second
                            if (speed > 1024 * 1024) {
                                speedText.textContent = (speed / (1024 * 1024)).toFixed(1) + ' MB/s';
                            } else {
                                speedText.textContent = (speed / 1024).toFixed(0) + ' KB/s';
                            }
                        }
                    } else {
                        text.textContent = 'Mengunggah...';
                    }
                }
            })
            .then(response => {
                text.textContent = 'Mengekstrak & Sinkronisasi...';
                bar.classList.remove('bg-indigo-500');
                bar.classList.add('bg-emerald-500', 'animate-pulse');
                
                // Set success message in session/cookie equivalent or trigger reload
                // Since Laravel returns redirect back with success flash, we reload to display it
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            })
            .catch(error => {
                console.error('Error uploading zip:', error);
                let errorMsg = 'Terjadi kesalahan saat mengunggah.';
                if (error.response?.data?.errors?.dataset_zip) {
                    errorMsg = error.response.data.errors.dataset_zip[0];
                } else if (error.response?.data?.message) {
                    errorMsg = error.response.data.message;
                }
                alert('Gagal mengunggah dataset: ' + errorMsg);
                container.classList.add('hidden');
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            });
        }
    </script>
</body>
</html>
