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
                    <p class="text-slate-400 mt-2 max-w-sm text-sm">Lanjut ke <a href="{{ route('audit.relabel') }}" class="text-amber-400 underline">putaran 2</a> untuk tentukan label tujuan kandidat kategori A.</p>
                </div>
                <img id="target-image" src="" alt="Kandidat audit" class="hidden max-h-[340px] max-w-full object-contain select-none transition-transform duration-300" onload="imageLoaded()" onerror="imageError()">
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
            awaitingNext = true; // kunci sebelum request, cegah double-submit
            document.getElementById('decision-controls').classList.add('opacity-30', 'pointer-events-none');
            const note = document.getElementById('note').value;

            axios.post('{{ route("api.audit.submit") }}', { candidate_id: activeCandidateId, decision, note })
                .then(response => {
                    const data = response.data;
                    document.getElementById('reveal-pred').textContent = data.predicted_label ?? '-';
                    document.getElementById('reveal-score').textContent = data.label_quality_score != null ? Number(data.label_quality_score).toFixed(4) : '-';
                    document.getElementById('reveal-box').classList.remove('hidden');
                })
                .catch(err => {
                    awaitingNext = false;
                    document.getElementById('decision-controls').classList.remove('opacity-30', 'pointer-events-none');
                    if (err.response && err.response.status === 409) {
                        showAlert(err.response.data.error, 'warning');
                        setTimeout(fetchNext, 1500);
                    } else {
                        showAlert('Gagal mengirim keputusan. Coba lagi.', 'danger');
                    }
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
