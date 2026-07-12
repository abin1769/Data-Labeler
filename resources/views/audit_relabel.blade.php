<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Labeler - Tentukan Label Benar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'], outfit: ['Outfit', 'sans-serif'] } } } }
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
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.97); } to { opacity: 1; transform: scale(1); } }
        button:disabled { opacity: 0.3; cursor: not-allowed; }
    </style>
</head>
<body class="min-h-screen text-slate-100 flex flex-col relative pb-8">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="absolute top-10 left-10 w-[500px] h-[500px] bg-red-500/5 rounded-full blur-[150px] pointer-events-none"></div>
    <div class="absolute bottom-10 right-10 w-[500px] h-[500px] bg-purple-500/5 rounded-full blur-[150px] pointer-events-none"></div>

    <header class="glass-header sticky top-0 z-50 py-4 px-6 md:px-12 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-red-500 to-amber-500 flex items-center justify-center shadow-lg shadow-red-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2" />
                </svg>
            </div>
            <div>
                <span class="font-outfit font-bold text-lg tracking-tight bg-gradient-to-r from-red-200 to-amber-200 bg-clip-text text-transparent">Data Labeler</span>
                <span class="text-[10px] uppercase font-bold tracking-widest text-red-400 bg-red-400/10 px-1.5 py-0.5 rounded ml-2">Audit Label -- Putaran 2</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('audit.index') }}" class="text-xs font-semibold text-slate-300 hover:text-amber-400 transition-colors duration-200">&larr; Putaran 1</a>
            <div class="flex items-center gap-2 bg-slate-900/60 border border-slate-700/50 rounded-xl py-1.5 px-4">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-semibold text-slate-300">Halo, <span class="text-white font-bold">{{ $nickname }}</span></span>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-4xl w-full mx-auto px-4 md:px-8 py-8 flex flex-col gap-6 z-10">

        <div class="glass-card rounded-2xl p-4 text-center">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sisa / Selesai</span>
            <div class="mt-1"><span id="stat-left" class="text-xl font-bold font-outfit text-red-300">-</span> <span class="text-slate-500 text-sm">/</span> <span id="stat-done" class="text-xl font-bold font-outfit text-emerald-300">-</span></div>
        </div>

        <div class="glass-card rounded-2xl p-4 text-xs text-slate-400">
            Kandidat di sini sudah <b class="text-red-400">dikonfirmasi salah label</b> di putaran 1. Tentukan kelas yang BENAR --
            atau pilih <b class="text-purple-400">Kontaminasi</b> kalau ternyata ini bukan foto sampah sama sekali (chart, diagram, dll).
        </div>

        <div class="glass-card rounded-3xl p-6 md:p-8 flex flex-col items-center justify-between min-h-[480px] relative shadow-2xl">
            <div class="absolute -top-px left-8 right-8 h-px bg-gradient-to-r from-transparent via-red-500/30 to-transparent"></div>

            <div id="alert-box" class="hidden absolute top-4 left-4 right-4 z-20 p-3.5 rounded-xl border text-sm font-semibold"><span id="alert-msg"></span></div>

            <div class="w-full flex-grow flex items-center justify-center min-h-[280px] mb-4 relative rounded-2xl bg-slate-950/40 border border-slate-800/80 overflow-hidden">
                <div id="image-loader" class="absolute inset-0 bg-slate-900/80 flex flex-col items-center justify-center gap-3">
                    <div class="w-10 h-10 border-4 border-red-500 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-xs font-medium text-red-300">Memuat gambar...</p>
                </div>
                <div id="complete-screen" class="hidden absolute inset-0 bg-slate-950/90 flex flex-col items-center justify-center p-8 text-center">
                    <div class="w-20 h-20 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h3 class="text-2xl font-extrabold font-outfit text-slate-100">Semua Kandidat A Sudah Direlabel!</h3>
                    <p class="text-slate-400 mt-2 max-w-sm text-sm">Data siap diunduh admin buat dieksekusi ke dataset.</p>
                </div>
                <img id="target-image" src="" alt="Kandidat relabel" class="hidden max-h-[340px] max-w-full object-contain select-none animate-fade-in" onload="imageLoaded()" onerror="imageError()">
            </div>

            <div class="w-full text-center mb-4">
                <span class="text-xs text-slate-400">Label lama (sudah dikonfirmasi SALAH): </span>
                <span id="given-label" class="font-bold text-red-400 line-through">-</span>
            </div>

            <div id="decision-controls" class="w-full grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($classOptions as $code => $desc)
                <button data-decision="{{ $code }}" onclick="submitDecision('{{ $code }}')"
                    class="option-btn group flex flex-col items-center justify-center p-4 bg-slate-900/40 hover:bg-emerald-500/10 border border-slate-800 hover:border-emerald-500/40 rounded-2xl transition-all duration-200 active:scale-95 text-center">
                    <span class="font-bold text-xs text-slate-200">{{ \Illuminate\Support\Str::before($desc, ' --') }}</span>
                </button>
                @endforeach
                <button data-decision="{{ $contaminationDecision }}" onclick="submitDecision('{{ $contaminationDecision }}')"
                    class="option-btn group flex flex-col items-center justify-center p-4 bg-purple-500/5 hover:bg-purple-500/15 border border-purple-500/20 hover:border-purple-500/40 rounded-2xl transition-all duration-200 active:scale-95 text-center">
                    <span class="font-bold text-xs text-purple-300">Kontaminasi (bukan sampah)</span>
                </button>
            </div>

            <div id="reveal-box" class="hidden w-full mt-4 p-4 bg-indigo-500/10 border border-indigo-500/25 rounded-2xl text-center">
                <p class="text-xs text-slate-400">Prediksi model (sebagai pembanding): <b id="reveal-pred" class="text-indigo-300"></b></p>
                <button onclick="fetchNext()" class="mt-3 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition-all">Lanjut &rarr;</button>
            </div>
        </div>

        <p class="text-center text-[11px] text-slate-500">Shortcut keyboard: <kbd class="bg-slate-800 px-1.5 py-0.5 rounded">1</kbd>-<kbd class="bg-slate-800 px-1.5 py-0.5 rounded">4</kbd> untuk memutuskan, <kbd class="bg-slate-800 px-1.5 py-0.5 rounded">Enter</kbd> untuk lanjut.</p>
    </main>

    <footer class="text-center pb-8 mt-4 text-[11px] text-slate-500 font-medium tracking-wide z-10">&copy; 2026 &bull; Tim BDC Satria Data Universitas Jambi</footer>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

        let activeCandidateId = null;
        let activeGivenLabel = null;
        let awaitingNext = false;

        document.addEventListener('DOMContentLoaded', () => {
            fetchNext();
            document.addEventListener('keydown', (e) => {
                if (awaitingNext) { if (e.key === 'Enter') fetchNext(); return; }
                const buttons = document.querySelectorAll('.option-btn:not(:disabled)');
                const idx = parseInt(e.key, 10) - 1;
                if (!isNaN(idx) && buttons[idx]) submitDecision(buttons[idx].dataset.decision);
            });
        });

        function fetchNext() {
            document.getElementById('reveal-box').classList.add('hidden');
            document.getElementById('decision-controls').classList.remove('opacity-30', 'pointer-events-none');
            awaitingNext = false;
            showLoader(true);
            hideAlert();

            axios.get('{{ route("api.audit.relabel.next") }}')
                .then(response => {
                    const data = response.data;
                    document.getElementById('stat-left').textContent = data.total_left ?? 0;
                    document.getElementById('stat-done').textContent = data.total_done ?? 0;

                    if (data.completed) {
                        showComplete(true); showLoader(false); activeCandidateId = null; return;
                    }
                    showComplete(false);
                    activeCandidateId = data.candidate.id;
                    activeGivenLabel = data.candidate.given_label;
                    document.getElementById('given-label').textContent = data.candidate.given_label;
                    document.getElementById('target-image').src = data.candidate.url;

                    // Nonaktifkan tombol yang kelasnya sama dengan label lama -- tidak
                    // masuk akal "koreksi" balik ke label yang sedang ditinjau.
                    document.querySelectorAll('.option-btn').forEach(btn => {
                        btn.disabled = btn.dataset.decision === activeGivenLabel;
                    });
                })
                .catch(err => { console.error(err); showAlert('Gagal mengambil kandidat berikutnya.', 'danger'); showLoader(false); });
        }

        function imageLoaded() { document.getElementById('target-image').classList.remove('hidden'); showLoader(false); }
        function imageError() { showLoader(false); showAlert('Gagal memuat gambar.', 'danger'); }

        function submitDecision(decision) {
            if (!activeCandidateId || awaitingNext) return;
            awaitingNext = true;
            document.getElementById('decision-controls').classList.add('opacity-30', 'pointer-events-none');

            axios.post('{{ route("api.audit.relabel.submit") }}', { candidate_id: activeCandidateId, decision })
                .then(response => {
                    const data = response.data;
                    document.getElementById('reveal-pred').textContent = data.predicted_label ?? '-';
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
