<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Labeler - Masuk</title>
    <!-- Google Fonts: Outfit & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS (via CDN for instant preview/styling or using Laravel's compilation. Since we want styling to look awesome immediately, we can use Tailwind CDN in layout) -->
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
    <style>
        body {
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.08), transparent 40%),
                        radial-gradient(circle at bottom left, rgba(168, 85, 247, 0.08), transparent 40%),
                        #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 overflow-hidden text-slate-100 relative">

    <!-- Glowing Background Orbs -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-indigo-500/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="w-full max-w-md z-10">
        <!-- Logo / Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-500 shadow-lg shadow-indigo-500/35 mb-4 transform hover:rotate-12 transition-transform duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <h1 class="text-4xl font-extrabold font-outfit tracking-tight bg-gradient-to-r from-indigo-200 via-slate-100 to-purple-200 bg-clip-text text-transparent">
                Data Labeler
            </h1>
            <p class="text-slate-400 mt-2 text-sm font-medium">Bantu kami mengklasifikasikan dataset gambar. Bantuan anda sangat berarti bagi kami</p>
        </div>

        <!-- Nickname Card -->
        <div class="glass-card rounded-3xl p-8 shadow-2xl relative">
            <div class="absolute -top-px left-8 right-8 h-px bg-gradient-to-r from-transparent via-indigo-500/50 to-transparent"></div>
            
            <form action="{{ route('nickname.set') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label for="passkey" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                            Passkey dari Admin
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 8a6 6 0 10-11.998-.125L6 8v1H5a1 1 0 000 2h1v2H5a1 1 0 100 2h1v1a1 1 0 001 1h2a1 1 0 100-2H8v-1h2a1 1 0 100-2H8v-2h1a1 1 0 100-2H8V8a4 4 0 118 0v1h-1a1 1 0 100 2h1v2h-1a1 1 0 100 2h1v1a1 1 0 001 1h2a1 1 0 100-2h-2v-1h1a1 1 0 100-2h-1v-2h1a1 1 0 100-2h-1V8z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <input 
                                type="password" 
                                name="passkey" 
                                id="passkey" 
                                required
                                maxlength="50"
                                placeholder="Masukkan passkey aktif" 
                                value="{{ old('passkey') }}"
                                class="w-full bg-slate-900/60 border border-slate-700/60 rounded-xl py-3.5 pl-12 pr-4 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 font-medium"
                                autocomplete="off"
                            >
                        </div>
                        @error('passkey')
                            <p class="text-red-400 text-xs mt-2 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="nickname" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                            Masukkan Nama / Nickname Kamu
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <input 
                                type="text" 
                                name="nickname" 
                                id="nickname" 
                                required
                                maxlength="50"
                                placeholder="Contoh: Abin, Budi, Sinta" 
                                value="{{ old('nickname') }}"
                                class="w-full bg-slate-900/60 border border-slate-700/60 rounded-xl py-3.5 pl-12 pr-4 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 font-medium"
                                autocomplete="off"
                            >
                        </div>
                        @error('nickname')
                            <p class="text-red-400 text-xs mt-2 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="prodi" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                            Masukkan Program Studi / Prodi
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0z" />
                                </svg>
                            </span>
                            <input 
                                type="text" 
                                name="prodi" 
                                id="prodi" 
                                required
                                maxlength="100"
                                placeholder="Contoh: Teknik Informatika, Sains Data" 
                                value="{{ old('prodi') }}"
                                class="w-full bg-slate-900/60 border border-slate-700/60 rounded-xl py-3.5 pl-12 pr-4 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 font-medium"
                                autocomplete="off"
                            >
                        </div>
                        @error('prodi')
                            <p class="text-red-400 text-xs mt-2 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full py-4 px-6 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-xl font-semibold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/35 transform hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200"
                >
                    Masuk ke Workspace Aktif
                </button>
            </form>
        </div>

        <!-- Info / Admin Link -->
        <div class="text-center mt-6 space-y-4">
            <div>
                <a href="{{ route('admin') }}" class="text-slate-500 hover:text-indigo-400 text-xs font-semibold uppercase tracking-wider transition-colors duration-200">
                    Masuk sebagai Admin &rarr;
                </a>
            </div>
            <div class="text-[11px] text-slate-500 font-medium tracking-wide">
                &copy; 2026 &bull; Tim BDC Satria Data Universitas Jambi
            </div>
        </div>
    </div>

</body>
</html>
