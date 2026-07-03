<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Labeler - Workspace</title>
    <!-- Google Fonts: Outfit & Plus Jakarta Sans -->
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
    <!-- Axios for HTTP Requests -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- Alpine.js for smooth reactive UI (optional, but vanilla JS is fine too. Let's write vanilla JS to keep it direct and performant, with custom animations) -->
    <style>
        body {
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.05), transparent 45%),
                        radial-gradient(circle at bottom left, rgba(168, 85, 247, 0.05), transparent 45%),
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
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
        .animate-fade-out {
            animation: fadeOut 0.2s ease-in forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.97); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes fadeOut {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.97); }
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.3);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 flex flex-col relative pb-8">

    <!-- CSRF Token for Axios -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Glowing Background Orbs -->
    <div class="absolute top-10 left-10 w-[500px] h-[500px] bg-indigo-500/5 rounded-full blur-[150px] pointer-events-none"></div>
    <div class="absolute bottom-10 right-10 w-[500px] h-[500px] bg-purple-500/5 rounded-full blur-[150px] pointer-events-none"></div>

    <!-- Navigation Header -->
    <header class="glass-header sticky top-0 z-50 py-4 px-6 md:px-12 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <span class="font-outfit font-bold text-lg tracking-tight bg-gradient-to-r from-indigo-200 to-purple-200 bg-clip-text text-transparent">Data Labeler</span>
                <span class="text-[10px] uppercase font-bold tracking-widest text-indigo-400 bg-indigo-400/10 px-1.5 py-0.5 rounded ml-2">Workspace</span>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 bg-slate-900/60 border border-slate-700/50 rounded-xl py-1.5 px-4">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-semibold text-slate-300">Halo, <span class="text-white font-bold">{{ $nickname }}</span> <span class="text-slate-400 text-[11px] ml-1">({{ $prodi }})</span></span>
            </div>
            
            <form action="{{ route('nickname.logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-xs font-semibold text-slate-400 hover:text-red-400 transition-colors duration-200">
                    Ganti Nama
                </button>
            </form>
        </div>
    </header>

    <!-- Main Workspace Layout -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 md:px-8 py-8 grid grid-cols-1 lg:grid-cols-12 gap-8 z-10">
        
        <!-- Left & Center Column: Workspace (8 Cols) -->
        <section class="lg:col-span-8 flex flex-col gap-6">
            
            <!-- Statistics Bar -->
            <div class="grid grid-cols-3 gap-4">
                <div class="glass-card rounded-2xl p-4 flex flex-col justify-center">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sisa Gambar</span>
                    <span id="stat-left" class="text-2xl font-bold font-outfit text-indigo-300 mt-1">-</span>
                </div>
                <div class="glass-card rounded-2xl p-4 flex flex-col justify-center">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Dilabel</span>
                    <span id="stat-total" class="text-2xl font-bold font-outfit text-purple-300 mt-1">-</span>
                </div>
                <div class="glass-card rounded-2xl p-4 flex flex-col justify-center">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kontribusi Anda</span>
                    <span id="stat-user" class="text-2xl font-bold font-outfit text-emerald-300 mt-1">-</span>
                </div>
            </div>

            <!-- Workspace Card -->
            <div class="glass-card rounded-3xl p-6 md:p-8 flex flex-col items-center justify-between min-h-[500px] relative shadow-2xl">
                <!-- Top subtle border -->
                <div class="absolute -top-px left-8 right-8 h-px bg-gradient-to-r from-transparent via-indigo-500/30 to-transparent"></div>

                <!-- Alert Message -->
                <div id="alert-box" class="hidden absolute top-4 left-4 right-4 z-20 p-3.5 rounded-xl border text-sm font-semibold transition-all duration-300 flex items-center gap-2">
                    <!-- Icon inject dynamically -->
                    <span id="alert-msg"></span>
                </div>

                <!-- Image Frame Container -->
                <div class="w-full flex-grow flex items-center justify-center min-h-[300px] mb-6 relative rounded-2xl bg-slate-950/40 border border-slate-800/80 overflow-hidden group">
                    
                    <!-- Loading Spinner -->
                    <div id="image-loader" class="absolute inset-0 bg-slate-900/80 flex flex-col items-center justify-center gap-3 transition-opacity duration-300">
                        <div class="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                        <p class="text-xs font-medium text-indigo-300">Memuat gambar...</p>
                    </div>

                    <!-- Complete Screen (Hidden initially) -->
                    <div id="complete-screen" class="hidden absolute inset-0 bg-slate-950/90 flex flex-col items-center justify-center p-8 text-center">
                        <div class="w-20 h-20 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-extrabold font-outfit text-slate-100">Semua Gambar Selesai Dilabel!</h3>
                        <p class="text-slate-400 mt-2 max-w-sm text-sm">Hebat! Semua dataset di folder telah berhasil dilabeli oleh kamu dan teman-temanmu. Silakan tunggu admin menyinkronkan gambar baru atau mengunduh hasilnya.</p>
                        <button onclick="fetchNextImage()" class="mt-6 px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-xs font-semibold rounded-xl transition-all duration-200 border border-slate-700">
                            Coba Segarkan
                        </button>
                    </div>

                    <!-- Active Image Element -->
                    <img 
                        id="target-image" 
                        src="" 
                        alt="Label Target" 
                        class="hidden max-h-[380px] max-w-full object-contain select-none transition-transform duration-300 group-hover:scale-[1.02]"
                        onload="imageLoaded()"
                        onerror="imageError()"
                    >
                </div>

                <!-- Interaction / Label Selection Section -->
                <div id="label-controls" class="w-full">
                    <p class="text-center text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Pilih Label Klasifikasi</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Label 0: Recycleable -->
                        <button 
                            onclick="submitLabel(0)" 
                            class="group relative flex flex-col items-center justify-center p-4 bg-gradient-to-br from-emerald-500/10 to-teal-600/5 hover:from-emerald-500/20 hover:to-teal-600/15 border border-emerald-500/20 hover:border-emerald-500/40 rounded-2xl transition-all duration-200 active:scale-95 text-center shadow-lg hover:shadow-emerald-500/5"
                        >
                            <span class="absolute top-2.5 right-3 text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-mono select-none">Q / 1</span>
                            <div class="w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2" />
                                </svg>
                            </div>
                            <span class="font-bold text-sm text-slate-200">Recycleable</span>
                            <span class="text-[10px] text-slate-400 mt-1">Daur Ulang (Label: 0)</span>
                        </button>

                        <!-- Label 1: Electronics -->
                        <button 
                            onclick="submitLabel(1)" 
                            class="group relative flex flex-col items-center justify-center p-4 bg-gradient-to-br from-blue-500/10 to-indigo-600/5 hover:from-blue-500/20 hover:to-indigo-600/15 border border-blue-500/20 hover:border-blue-500/40 rounded-2xl transition-all duration-200 active:scale-95 text-center shadow-lg hover:shadow-blue-500/5"
                        >
                            <span class="absolute top-2.5 right-3 text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-300 font-mono select-none">W / 2</span>
                            <div class="w-10 h-10 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <span class="font-bold text-sm text-slate-200">Electronics</span>
                            <span class="text-[10px] text-slate-400 mt-1">Elektronik (Label: 1)</span>
                        </button>

                        <!-- Label 2: Organics -->
                        <button 
                            onclick="submitLabel(2)" 
                            class="group relative flex flex-col items-center justify-center p-4 bg-gradient-to-br from-amber-500/10 to-orange-600/5 hover:from-amber-500/20 hover:to-orange-600/15 border border-amber-500/20 hover:border-amber-500/40 rounded-2xl transition-all duration-200 active:scale-95 text-center shadow-lg hover:shadow-amber-500/5"
                        >
                            <span class="absolute top-2.5 right-3 text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 font-mono select-none">E / 3</span>
                            <div class="w-10 h-10 rounded-full bg-amber-500/10 text-amber-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                            </div>
                            <span class="font-bold text-sm text-slate-200">Organics</span>
                            <span class="text-[10px] text-slate-400 mt-1">Organik (Label: 2)</span>
                        </button>
                    </div>
                </div>

            </div>
        </section>

        <!-- Right Column: Guidelines & Leaderboard (4 Cols) -->
        <section class="lg:col-span-4 flex flex-col gap-6">
            
            <!-- Guideline Box -->
            <div class="glass-card rounded-3xl p-6 relative">
                <div class="absolute -top-px left-6 right-6 h-px bg-gradient-to-r from-transparent via-indigo-500/30 to-transparent"></div>
                <h3 class="font-outfit font-bold text-lg mb-4 flex items-center gap-2 text-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    Pedoman Pelabelan
                </h3>
                
                <div class="space-y-4">
                    <!-- Recycleable Info -->
                    <div class="flex flex-col bg-slate-900/40 border border-slate-800/80 rounded-xl p-3">
                        <div class="flex gap-3">
                            <span class="w-1.5 h-auto bg-emerald-500 rounded-full flex-shrink-0"></span>
                            <div>
                                <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wide">Recycleable (0)</h4>
                                <p class="text-xs text-slate-400 mt-1">Sampah yang bisa didaur ulang. Contoh: Botol plastik, kardus bekas, kaleng minuman, kertas, koran, botol beling.</p>
                            </div>
                        </div>
                        @if(!empty($examples[0]))
                            <div class="grid grid-cols-4 gap-1.5 mt-2.5 ml-4.5 pl-3">
                                @foreach($examples[0] as $imgUrl)
                                    <div class="relative group aspect-square rounded-lg bg-slate-950/40 border border-slate-800/80 overflow-hidden cursor-zoom-in">
                                        <img src="{{ $imgUrl }}" class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-110" onclick="showLightbox('{{ $imgUrl }}')">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Electronics Info -->
                    <div class="flex flex-col bg-slate-900/40 border border-slate-800/80 rounded-xl p-3">
                        <div class="flex gap-3">
                            <span class="w-1.5 h-auto bg-blue-500 rounded-full flex-shrink-0"></span>
                            <div>
                                <h4 class="text-xs font-bold text-blue-400 uppercase tracking-wide">Electronics (1)</h4>
                                <p class="text-xs text-slate-400 mt-1">Komponen elektronik & e-waste. Contoh: Kabel rusak, HP, baterai bekas, keyboard, mouse, charger, bohlam.</p>
                            </div>
                        </div>
                        @if(!empty($examples[1]))
                            <div class="grid grid-cols-4 gap-1.5 mt-2.5 ml-4.5 pl-3">
                                @foreach($examples[1] as $imgUrl)
                                    <div class="relative group aspect-square rounded-lg bg-slate-950/40 border border-slate-800/80 overflow-hidden cursor-zoom-in">
                                        <img src="{{ $imgUrl }}" class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-110" onclick="showLightbox('{{ $imgUrl }}')">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Organics Info -->
                    <div class="flex flex-col bg-slate-900/40 border border-slate-800/80 rounded-xl p-3">
                        <div class="flex gap-3">
                            <span class="w-1.5 h-auto bg-amber-500 rounded-full flex-shrink-0"></span>
                            <div>
                                <h4 class="text-xs font-bold text-amber-400 uppercase tracking-wide">Organics (2)</h4>
                                <p class="text-xs text-slate-400 mt-1">Sampah organik / mudah terurai. Contoh: Sisa makanan, kulit buah, potongan sayur, dedaunan gugur, cangkang telur.</p>
                            </div>
                        </div>
                        @if(!empty($examples[2]))
                            <div class="grid grid-cols-4 gap-1.5 mt-2.5 ml-4.5 pl-3">
                                @foreach($examples[2] as $imgUrl)
                                    <div class="relative group aspect-square rounded-lg bg-slate-950/40 border border-slate-800/80 overflow-hidden cursor-zoom-in">
                                        <img src="{{ $imgUrl }}" class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-110" onclick="showLightbox('{{ $imgUrl }}')">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Leaderboard Box -->
            <div class="glass-card rounded-3xl p-6 relative flex flex-col flex-grow min-h-[300px]">
                <div class="absolute -top-px left-6 right-6 h-px bg-gradient-to-r from-transparent via-purple-500/30 to-transparent"></div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-outfit font-bold text-lg flex items-center gap-2 text-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        Leaderboard Live
                    </h3>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-500 bg-slate-800/60 px-2 py-0.5 rounded border border-slate-700/30">Live updates</span>
                </div>

                <!-- Leaderboard list container -->
                <div class="flex-grow flex flex-col gap-2 overflow-y-auto max-h-[320px] pr-1" id="leaderboard-list">
                    <!-- Dynamic updates inject here -->
                    <div class="py-12 text-center text-slate-500 text-xs font-medium">Memuat peringkat...</div>
                </div>
            </div>

        </section>

        <!-- Footer Copyright -->
        <footer class="text-center pb-8 mt-4 text-[11px] text-slate-500 font-medium tracking-wide">
            &copy; 2026 &bull; Tim BDC Satria Data Universitas Jambi
        </footer>
    </main>

    <!-- Interactive JS Script -->
    <script>
        // Setup Axios default headers
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

        let activeImageId = null;
        let isSubmitting = false;

        // Run on page load
        document.addEventListener('DOMContentLoaded', () => {
            fetchNextImage();
            updateLeaderboard();
            // Poll leaderboard every 5 seconds
            setInterval(updateLeaderboard, 5000);

            // Bind keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                // Return if complete screen is showing or user is typing in an input
                if (document.getElementById('complete-screen').classList.contains('hidden') === false) return;
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

                // Support both (0, 1, 2) keys and (1, 2, 3 / Q, W, E) keys
                if (e.key === '0' || e.key === '1' || e.key === 'q' || e.key === 'Q') {
                    submitLabel(0);
                } else if (e.key === '2' || e.key === 'w' || e.key === 'W') {
                    submitLabel(1);
                } else if (e.key === '3' || e.key === 'e' || e.key === 'E') {
                    submitLabel(2);
                }
            });
        });

        // Fetch Next Image
        function fetchNextImage() {
            showLoader(true);
            hideAlert();

            axios.get('{{ route("api.next-image") }}')
                .then(response => {
                    const data = response.data;
                    
                    // Update Stat HUDs
                    document.getElementById('stat-left').textContent = formatNumber(data.total_left);
                    document.getElementById('stat-total').textContent = formatNumber(data.total_labeled);
                    document.getElementById('stat-user').textContent = formatNumber(data.user_labeled);

                    if (data.completed) {
                        showCompleteScreen(true);
                        showLoader(false);
                        activeImageId = null;
                    } else {
                        showCompleteScreen(false);
                        activeImageId = data.image.id;
                        
                        const imgEl = document.getElementById('target-image');
                        imgEl.src = data.image.url;
                        // Onload triggers imageLoaded() which turns off the loader
                    }
                })
                .catch(error => {
                    console.error('Error fetching image:', error);
                    showAlert('Gagal mengambil gambar berikutnya. Silakan segarkan halaman.', 'danger');
                    showLoader(false);
                });
        }

        // Image loaded successfully
        function imageLoaded() {
            const imgEl = document.getElementById('target-image');
            imgEl.classList.remove('hidden');
            imgEl.classList.remove('animate-fade-out');
            imgEl.classList.add('animate-fade-in');
            showLoader(false);
        }

        // Image loading error
        function imageError() {
            showLoader(false);
            showAlert('Gagal memuat gambar. Kemungkinan file di local folder D:\\SATRIA DATA\\test tidak dapat diakses atau sudah dipindahkan.', 'danger');
        }

        // Helper: Format large numbers
        function formatNumber(num) {
            return num !== undefined && num !== null ? num.toLocaleString('id-ID') : '-';
        }

        // Helper: Show/Hide Loader
        function showLoader(show) {
            const loader = document.getElementById('image-loader');
            if (show) {
                loader.style.opacity = '1';
                loader.style.pointerEvents = 'auto';
            } else {
                loader.style.opacity = '0';
                loader.style.pointerEvents = 'none';
            }
        }

        // Helper: Show/Hide Complete Screen
        function showCompleteScreen(show) {
            const cs = document.getElementById('complete-screen');
            const controls = document.getElementById('label-controls');
            const img = document.getElementById('target-image');

            if (show) {
                cs.classList.remove('hidden');
                controls.classList.add('opacity-40');
                controls.classList.add('pointer-events-none');
                img.classList.add('hidden');
            } else {
                cs.classList.add('hidden');
                controls.classList.remove('opacity-40');
                controls.classList.remove('pointer-events-none');
            }
        }

        // Submit Label
        function submitLabel(labelValue) {
            if (!activeImageId || isSubmitting) return;

            isSubmitting = true;
            const imgEl = document.getElementById('target-image');
            
            // Fade out the current image before request finishes to feel snappy!
            imgEl.classList.remove('animate-fade-in');
            imgEl.classList.add('animate-fade-out');

            axios.post('{{ route("api.submit-label") }}', {
                image_id: activeImageId,
                label: labelValue
            })
            .then(response => {
                isSubmitting = false;
                // Fetch next image
                fetchNextImage();
                // Refresh leaderboard
                updateLeaderboard();
            })
            .catch(error => {
                isSubmitting = false;
                imgEl.classList.remove('animate-fade-out');
                imgEl.classList.add('animate-fade-in');

                if (error.response && error.response.status === 409) {
                    // Conflicted: someone else labeled it
                    showAlert(error.response.data.error, 'warning');
                    setTimeout(fetchNextImage, 2000);
                } else {
                    showAlert('Gagal mengirimkan label. Silakan coba lagi.', 'danger');
                    console.error('Error submitting label:', error);
                }
            });
        }

        // Update Leaderboard via AJAX
        function updateLeaderboard() {
            axios.get('{{ route("api.leaderboard") }}')
                .then(response => {
                    const leaderboard = response.data;
                    const container = document.getElementById('leaderboard-list');
                    container.innerHTML = '';

                    if (leaderboard.length === 0) {
                        container.innerHTML = '<div class="py-12 text-center text-slate-500 text-xs font-medium">Belum ada label terkumpul. Jadilah yang pertama!</div>';
                        return;
                    }

                    leaderboard.forEach((user, index) => {
                        let medal = '';
                        let textClass = 'text-slate-300';
                        let rankBg = 'bg-slate-800/40 border border-slate-700/30';

                        if (index === 0) {
                            medal = '🥇';
                            textClass = 'text-yellow-300 font-bold';
                            rankBg = 'bg-yellow-500/10 border border-yellow-500/25';
                        } else if (index === 1) {
                            medal = '🥈';
                            textClass = 'text-slate-100 font-bold';
                            rankBg = 'bg-slate-300/10 border border-slate-300/20';
                        } else if (index === 2) {
                            medal = '🥉';
                            textClass = 'text-amber-500 font-bold';
                            rankBg = 'bg-amber-600/10 border border-amber-600/20';
                        }

                        const userRow = `
                            <div class="flex items-center justify-between p-3.5 ${rankBg} rounded-xl transition-all duration-300 hover:translate-x-1">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 text-center text-sm font-semibold">${medal || (index + 1)}</span>
                                    <span class="text-sm font-medium ${textClass}">${escapeHtml(user.labeled_by)}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-bold text-slate-200">${formatNumber(user.total)}</span>
                                    <span class="text-[9px] font-semibold text-slate-500 uppercase">img</span>
                                </div>
                            </div>
                        `;
                        container.insertAdjacentHTML('beforeend', userRow);
                    });
                })
                .catch(error => {
                    console.error('Error fetching leaderboard:', error);
                });
        }

        // Helper: Alert Display
        function showAlert(message, type) {
            const alertBox = document.getElementById('alert-box');
            const alertMsg = document.getElementById('alert-msg');
            
            alertMsg.textContent = message;
            alertBox.classList.remove('hidden', 'bg-red-500/15', 'border-red-500/35', 'text-red-400', 'bg-amber-500/15', 'border-amber-500/35', 'text-amber-400');
            
            if (type === 'danger') {
                alertBox.classList.add('bg-red-500/15', 'border-red-500/35', 'text-red-400');
            } else if (type === 'warning') {
                alertBox.classList.add('bg-amber-500/15', 'border-amber-500/35', 'text-amber-400');
            } else {
                alertBox.classList.add('bg-indigo-500/15', 'border-indigo-500/35', 'text-indigo-300');
            }
        }

        function hideAlert() {
            document.getElementById('alert-box').classList.add('hidden');
        }

        // Helper: Escape HTML to avoid XSS in live polling names
        function escapeHtml(str) {
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Lightbox Functions for Guideline Examples
        function showLightbox(imgUrl) {
            const modal = document.getElementById('lightbox-modal');
            const img = document.getElementById('lightbox-img');
            img.src = imgUrl;
            modal.classList.remove('hidden');
        }

        function hideLightbox() {
            const modal = document.getElementById('lightbox-modal');
            modal.classList.add('hidden');
        }
    </script>

    <!-- Lightbox Modal Overlay -->
    <div id="lightbox-modal" class="hidden fixed inset-0 bg-slate-950/90 z-[100] flex items-center justify-center p-4 cursor-zoom-out" onclick="hideLightbox()">
        <img id="lightbox-img" src="" class="max-h-[85vh] max-w-full rounded-2xl object-contain shadow-2xl border border-slate-800 animate-fade-in">
    </div>
</body>
</html>
