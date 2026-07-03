<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Labeler - Admin Login</title>
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
    <style>
        body {
            background: radial-gradient(circle at top right, rgba(239, 68, 68, 0.05), transparent 40%),
                        radial-gradient(circle at bottom left, rgba(99, 102, 241, 0.06), transparent 40%),
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
<body class="min-h-screen flex items-center justify-center p-4 text-slate-100 relative">

    <!-- Glowing Background Orbs -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-red-500/5 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-indigo-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="w-full max-w-md z-10">
        <!-- Logo / Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-red-500 to-indigo-600 shadow-lg shadow-red-500/20 mb-4 transform hover:rotate-12 transition-transform duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold font-outfit tracking-tight bg-gradient-to-r from-red-200 via-slate-100 to-indigo-200 bg-clip-text text-transparent">
                Admin Panel
            </h1>
            <p class="text-slate-400 mt-2 text-sm font-medium">Validasi label & unduh dataset CSV</p>
        </div>

        <!-- Login Card -->
        <div class="glass-card rounded-3xl p-8 shadow-2xl relative">
            <div class="absolute -top-px left-8 right-8 h-px bg-gradient-to-r from-transparent via-red-500/50 to-transparent"></div>
            
            <form action="{{ route('admin.login') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                        Password Admin
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            required
                            placeholder="Masukkan password admin..." 
                            class="w-full bg-slate-900/60 border border-slate-700/60 rounded-xl py-3.5 pl-12 pr-4 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 font-medium"
                            autocomplete="current-password"
                            autofocus
                        >
                    </div>
                    @error('password')
                        <p class="text-red-400 text-xs mt-2 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <button 
                    type="submit" 
                    class="w-full py-4 px-6 bg-gradient-to-r from-red-500 to-indigo-600 hover:from-red-600 hover:to-indigo-700 text-white rounded-xl font-semibold shadow-lg shadow-red-500/20 hover:shadow-red-500/35 transform hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200"
                >
                    Masuk Panel Admin
                </button>
            </form>
        </div>

        <!-- Back Link -->
        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-slate-500 hover:text-slate-300 text-xs font-semibold uppercase tracking-wider transition-colors duration-200">
                &larr; Kembali ke Workspace Label
            </a>
        </div>
    </div>

</body>
</html>
