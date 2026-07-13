<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Labeler - Audit Label</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'], outfit: ['Outfit', 'sans-serif'] } } }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- Cropper.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <style>
        body {
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.05), transparent 45%),
                        radial-gradient(circle at bottom left, rgba(168, 85, 247, 0.05), transparent 45%),
                        #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .glass-card { background: rgba(30, 41, 59, 0.55); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.06); }
        .glass-header { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
        .animate-fade-out { animation: fadeOut 0.2s ease-in forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.97); } to { opacity: 1; transform: scale(1); } }
        @keyframes fadeOut { from { opacity: 1; transform: scale(1); } to { opacity: 0; transform: scale(0.97); } }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.3); }
        ::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }
        button:disabled { opacity: 0.35; cursor: not-allowed; }
    </style>
</head>
<body class="min-h-screen text-slate-100 flex flex-col relative pb-8">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="absolute top-10 left-10 w-[500px] h-[500px] bg-indigo-500/5 rounded-full blur-[150px] pointer-events-none"></div>
    <div class="absolute bottom-10 right-10 w-[500px] h-[500px] bg-purple-500/5 rounded-full blur-[150px] pointer-events-none"></div>

    <header class="glass-header sticky top-0 z-50 py-4 px-6 md:px-12 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-red-500 flex items-center justify-center shadow-lg shadow-amber-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <span class="font-outfit font-bold text-lg tracking-tight bg-gradient-to-r from-amber-200 to-red-200 bg-clip-text text-transparent">Data Labeler</span>
                <span class="text-[10px] uppercase font-bold tracking-widest text-amber-400 bg-amber-400/10 px-1.5 py-0.5 rounded ml-2">Audit Label -- Putaran 1</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-300 hover:text-indigo-400 transition-colors duration-200">Workspace Label</a>
            <div class="flex items-center gap-2 bg-slate-900/60 border border-slate-700/50 rounded-xl py-1.5 px-4">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-semibold text-slate-300">Halo, <span class="text-white font-bold">{{ $nickname }}</span></span>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-5xl w-full mx-auto px-4 md:px-8 py-8 flex flex-col gap-6 z-10">

        <div class="grid grid-cols-2 gap-4">
            <div class="glass-card rounded-2xl p-4 flex flex-col justify-center">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sisa Ditinjau</span>
                <span id="stat-left" class="text-2xl font-bold font-outfit text-amber-300 mt-1">-</span>
            </div>
            <div class="glass-card rounded-2xl p-4 flex flex-col justify-center">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sudah Ditinjau</span>
                <span id="stat-done" class="text-2xl font-bold font-outfit text-emerald-300 mt-1">-</span>
            </div>
        </div>

        <!-- Rubrik -- selalu terlihat, jadi tidak perlu dihafal -->
        <div class="glass-card rounded-2xl p-5">
            <p class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3">Rubrik Keputusan</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs text-slate-400">
                @foreach($rubric as $code => $desc)
                <div><b class="text-amber-400">{{ $code }}</b> -- {{ $desc }}</div>
                @endforeach
            </div>
        </div>

        <div class="glass-card rounded-3xl p-6 md:p-8 flex flex-col items-center justify-between min-h-[480px] relative shadow-2xl">
            <div class="absolute -top-px left-8 right-8 h-px bg-gradient-to-r from-transparent via-amber-500/30 to-transparent"></div>

            <div id="alert-box" class="hidden absolute top-4 left-4 right-4 z-20 p-3.5 rounded-xl border text-sm font-semibold transition-all duration-300">
                <span id="alert-msg"></span>
            </div>

            <div class="w-full flex-grow flex items-center justify-center min-h-[280px] mb-4 relative rounded-2xl bg-slate-950/40 border border-slate-800/80 overflow-hidden group">
                <div id="image-loader" class="absolute inset-0 bg-slate-900/80 flex flex-col items-center justify-center gap-3 transition-opacity duration-300">
                    <div class="w-10 h-10 border-4 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-xs font-medium text-amber-300">Memuat gambar...</p>
                </div>
                <div id="complete-screen" class="hidden absolute inset-0 bg-slate-950/90 flex flex-col items-center justify-center p-8 text-center">
                    <div class="w-20 h-20 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-extrabold font-outfit text-slate-100">Semua Kandidat Sudah Ditinjau!</h3>
                    <p class="text-slate-400 mt-2 max-w-sm text-sm">Audit selesai! Silakan hubungi Admin untuk mengunduh hasil audit terbaru dan menerapkannya ke dataset.</p>
                </div>
                <img id="target-image" src="" alt="Kandidat audit" class="hidden max-h-[340px] max-w-full object-contain select-none transition-transform duration-300" onload="imageLoaded()" onerror="imageError()">
            </div>

            <!-- Crop Controls -->
            <div id="crop-actions" class="hidden w-full flex justify-center gap-3 mb-4">
                <button onclick="saveCrop()" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all">Simpan Crop</button>
                <button onclick="cancelCrop()" class="px-5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold transition-all">Batal</button>
            </div>
            
            <div id="crop-trigger-container" class="w-full flex justify-center mb-2">
                <button id="btn-crop" onclick="startCropping()" class="px-3.5 py-1.5 bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 hover:text-indigo-400 rounded-xl text-[11px] font-medium flex items-center gap-1.5 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z" /></svg>
                    Potong / Crop Gambar
                </button>
            </div>

            <div class="w-full text-center mb-2">
                <span class="text-xs text-slate-400">Label saat ini: </span>
                <span id="given-label" class="font-bold text-slate-100">-</span>
            </div>

            <div id="clustering-info" class="hidden w-full flex flex-wrap justify-center gap-2 mb-4">
                <span class="text-[11px] bg-slate-900/60 border border-slate-800 rounded-xl px-3 py-1.5 text-slate-300">
                    Klaster: <b id="info-cluster" class="text-indigo-400">-</b>
                </span>
                <span class="text-[11px] bg-slate-900/60 border border-slate-800 rounded-xl px-3 py-1.5 text-slate-300">
                    Konflik Tetangga: <b id="info-conflict" class="text-red-400">-</b>
                </span>
                <span class="text-[11px] bg-slate-900/60 border border-slate-800 rounded-xl px-3 py-1.5 text-slate-300">
                    Tetangga Dominan: <b id="info-dominant" class="text-emerald-400">-</b>
                </span>
            </div>

            <!-- Relabel Options (Inline Putaran 2) -->
            <div id="relabel-options" class="hidden w-full p-5 bg-amber-500/5 border border-amber-500/20 rounded-2xl mb-4 text-center">
                <p class="text-xs font-bold text-amber-300 uppercase tracking-wider mb-3">Tentukan Kelas Tujuan yang Benar</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <button onclick="submitDecisionWithClass('0_Recyclable')" class="py-2.5 px-4 bg-slate-900/80 hover:bg-amber-500/10 border border-slate-800 hover:border-amber-500/40 rounded-xl text-xs font-bold text-slate-200 transition-all active:scale-95">0_Recyclable</button>
                    <button onclick="submitDecisionWithClass('1_Electronic')" class="py-2.5 px-4 bg-slate-900/80 hover:bg-amber-500/10 border border-slate-800 hover:border-amber-500/40 rounded-xl text-xs font-bold text-slate-200 transition-all active:scale-95">1_Electronic</button>
                    <button onclick="submitDecisionWithClass('2_Organic')" class="py-2.5 px-4 bg-slate-900/80 hover:bg-amber-500/10 border border-slate-800 hover:border-amber-500/40 rounded-xl text-xs font-bold text-slate-200 transition-all active:scale-95">2_Organic</button>
                </div>
                <button onclick="cancelRelabelChoice()" class="mt-4 text-[10px] text-slate-400 hover:underline">Batal</button>
            </div>

            <div id="decision-controls" class="w-full">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($rubric as $code => $desc)
                    <button data-decision="{{ $code }}" onclick="submitDecision('{{ $code }}')"
                        class="group relative flex flex-col items-center justify-center p-4 bg-slate-900/40 hover:bg-amber-500/10 border border-slate-800 hover:border-amber-500/40 rounded-2xl transition-all duration-200 active:scale-95 text-center">
                        <span class="text-lg font-extrabold text-amber-400">{{ $code }}</span>
                        <span class="text-[10px] text-slate-400 mt-1 leading-snug">{{ \Illuminate\Support\Str::before($desc, ' --') }}</span>
                    </button>
                    @endforeach
                </div>
                <textarea id="note" placeholder="Catatan singkat (opsional)..." class="w-full mt-3 bg-slate-900/60 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-amber-500"></textarea>
            </div>

            <div id="reveal-box" class="hidden w-full mt-4 p-4 bg-indigo-500/10 border border-indigo-500/25 rounded-2xl text-center">
                <p class="text-xs text-slate-400">Prediksi model: <b id="reveal-pred" class="text-indigo-300"></b> (skor: <span id="reveal-score" class="text-indigo-300"></span>)</p>
                <button onclick="fetchNext()" class="mt-3 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition-all">Lanjut &rarr;</button>
            </div>
        </div>

        <p class="text-center text-[11px] text-slate-500">Shortcut keyboard: <kbd class="bg-slate-800 px-1.5 py-0.5 rounded">A</kbd> <kbd class="bg-slate-800 px-1.5 py-0.5 rounded">B</kbd> <kbd class="bg-slate-800 px-1.5 py-0.5 rounded">C</kbd> <kbd class="bg-slate-800 px-1.5 py-0.5 rounded">D</kbd> untuk memutuskan, <kbd class="bg-slate-800 px-1.5 py-0.5 rounded">Enter</kbd> untuk lanjut.</p>
    </main>

    <footer class="text-center pb-8 mt-4 text-[11px] text-slate-500 font-medium tracking-wide z-10">&copy; 2026 &bull; Tim BDC Satria Data Universitas Jambi</footer>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

        let activeCandidateId = null;
        let awaitingNext = false;
        let cropper = null;

        document.addEventListener('DOMContentLoaded', () => {
            fetchNext();
            document.addEventListener('keydown', (e) => {
                if (document.activeElement.tagName === 'TEXTAREA') return;
                const key = e.key.toUpperCase();
                if (['A', 'B', 'C', 'D'].includes(key) && !awaitingNext && activeCandidateId) {
                    submitDecision(key);
                } else if (key === 'ENTER' && awaitingNext) {
                    fetchNext();
                }
            });
        });

        function fetchNext() {
            document.getElementById('reveal-box').classList.add('hidden');
            document.getElementById('decision-controls').classList.remove('opacity-30', 'pointer-events-none');
            awaitingNext = false;
            showLoader(true);
            hideAlert();

            axios.get('{{ route("api.audit.next") }}')
                .then(response => {
                    const data = response.data;
                    document.getElementById('stat-left').textContent = data.total_left ?? 0;
                    document.getElementById('stat-done').textContent = data.total_done ?? 0;

                    if (data.completed) {
                        showComplete(true);
                        showLoader(false);
                        activeCandidateId = null;
                        return;
                    }
                    showComplete(false);
                    activeCandidateId = data.candidate.id;
                    document.getElementById('given-label').textContent = data.candidate.given_label;
                    
                    if (data.candidate.sub_cluster_id) {
                        document.getElementById('clustering-info').classList.remove('hidden');
                        document.getElementById('info-cluster').textContent = data.candidate.sub_cluster_id;
                        document.getElementById('info-conflict').textContent = (data.candidate.neighbor_conflict_rate * 100).toFixed(0) + '%';
                        document.getElementById('info-dominant').textContent = data.candidate.dominant_neighbor_class;
                    } else {
                        document.getElementById('clustering-info').classList.add('hidden');
                    }
                    
                    document.getElementById('note').value = '';
                    const img = document.getElementById('target-image');
                    img.src = data.candidate.url;
                })
                .catch(err => { console.error(err); showAlert('Gagal mengambil kandidat berikutnya.', 'danger'); showLoader(false); });
        }

        function imageLoaded() {
            const img = document.getElementById('target-image');
            img.classList.remove('hidden'); img.classList.add('animate-fade-in');
            showLoader(false);
        }
        function imageError() { showLoader(false); showAlert('Gagal memuat gambar. Cek apakah gambar train sudah diunggah admin.', 'danger'); }

        function submitDecision(decision) {
            if (!activeCandidateId || awaitingNext) return;
            
            // Jika memilih Salah Label (A), tampilkan pilihan sub-kelas (Putaran 2 terintegrasi)
            if (decision === 'A') {
                document.getElementById('decision-controls').classList.add('hidden');
                document.getElementById('crop-trigger-container').classList.add('hidden');
                document.getElementById('relabel-options').classList.remove('hidden');
                return;
            }

            // Untuk keputusan B, C, D, langsung submit ke backend
            executeSubmit(decision, null);
        }

        function submitDecisionWithClass(targetClass) {
            executeSubmit('A', targetClass);
        }

        function cancelRelabelChoice() {
            document.getElementById('relabel-options').classList.add('hidden');
            document.getElementById('decision-controls').classList.remove('hidden');
            document.getElementById('crop-trigger-container').classList.remove('hidden');
            awaitingNext = false;
        }

        function executeSubmit(decision, relabelTo) {
            awaitingNext = true;
            document.getElementById('decision-controls').classList.add('opacity-30', 'pointer-events-none');
            const note = document.getElementById('note').value;

            axios.post('{{ route("api.audit.submit") }}', { 
                candidate_id: activeCandidateId, 
                decision, 
                relabel_to: relabelTo, 
                note 
            })
                .then(response => {
                    const data = response.data;
                    document.getElementById('relabel-options').classList.add('hidden');
                    document.getElementById('decision-controls').classList.remove('hidden');
                    document.getElementById('crop-trigger-container').classList.remove('hidden');
                    
                    document.getElementById('reveal-pred').textContent = data.predicted_label ?? '-';
                    document.getElementById('reveal-score').textContent = data.label_quality_score != null ? Number(data.label_quality_score).toFixed(4) : '-';
                    document.getElementById('reveal-box').classList.remove('hidden');
                })
                .catch(err => {
                    awaitingNext = false;
                    document.getElementById('decision-controls').classList.remove('opacity-30', 'pointer-events-none', 'hidden');
                    document.getElementById('crop-trigger-container').classList.remove('hidden');
                    document.getElementById('relabel-options').classList.add('hidden');
                    
                    if (err.response && err.response.status === 409) {
                        showAlert(err.response.data.error, 'warning');
                        setTimeout(fetchNext, 1500);
                    } else {
                        showAlert('Gagal mengirim keputusan. Coba lagi.', 'danger');
                    }
                });
        }

        // ==========================================
        // CROP IMAGE FUNCTIONALITY (Cropper.js)
        // ==========================================
        function startCropping() {
            const image = document.getElementById('target-image');
            if (!image || image.classList.contains('hidden')) return;

            document.getElementById('decision-controls').classList.add('hidden');
            document.getElementById('crop-trigger-container').classList.add('hidden');
            document.getElementById('crop-actions').classList.remove('hidden');

            cropper = new Cropper(image, {
                viewMode: 1,
                autoCropArea: 0.8,
                responsive: true,
                background: false,
                zoomable: false,
            });
        }

        function cancelCrop() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            document.getElementById('crop-actions').classList.add('hidden');
            document.getElementById('decision-controls').classList.remove('hidden');
            document.getElementById('crop-trigger-container').classList.remove('hidden');
        }

        function saveCrop() {
            if (!cropper || !activeCandidateId) return;

            const canvas = cropper.getCroppedCanvas({
                maxWidth: 1024,
                maxHeight: 1024,
            });

            if (!canvas) {
                showAlert('Gagal mengambil data crop gambar.', 'danger');
                return;
            }

            const base64Image = canvas.toDataURL('image/jpeg', 0.9);

            showLoader(true);
            axios.post('{{ route("api.audit.crop") }}', {
                candidate_id: activeCandidateId,
                cropped_image: base64Image
            })
                .then(response => {
                    const img = document.getElementById('target-image');
                    // Tambah cache buster t=timestamp agar browser me-reload gambar terbaru
                    const currentUrl = new URL(img.src);
                    currentUrl.searchParams.set('t', new Date().getTime());
                    img.src = currentUrl.toString();

                    cancelCrop();
                    showAlert('Gambar berhasil di-crop!', 'success');
                    setTimeout(hideAlert, 2000);
                })
                .catch(err => {
                    showLoader(false);
                    console.error(err);
                    showAlert('Gagal menyimpan hasil crop gambar.', 'danger');
                });
        }

        function showLoader(show) {
            const l = document.getElementById('image-loader');
            l.style.opacity = show ? '1' : '0';
            l.style.pointerEvents = show ? 'auto' : 'none';
        }
        function showComplete(show) {
            document.getElementById('complete-screen').classList.toggle('hidden', !show);
            document.getElementById('target-image').classList.toggle('hidden', show);
            document.getElementById('decision-controls').classList.toggle('hidden', show);
        }
        function showAlert(msg, type) {
            const box = document.getElementById('alert-box');
            document.getElementById('alert-msg').textContent = msg;
            box.classList.remove('hidden', 'bg-red-500/15', 'border-red-500/35', 'text-red-400', 'bg-amber-500/15', 'border-amber-500/35', 'text-amber-400');
            box.classList.add(type === 'danger' ? 'bg-red-500/15' : 'bg-amber-500/15', type === 'danger' ? 'border-red-500/35' : 'border-amber-500/35', type === 'danger' ? 'text-red-400' : 'text-amber-400');
        }
        function hideAlert() { document.getElementById('alert-box').classList.add('hidden'); }
    </script>
</body>
</html>
