<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Healthcare Portal API | System Status</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>

<body class="bg-slate-900 text-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-4xl w-full">
        <!-- Header -->
        <div class="text-center mb-12">
            <div
                class="inline-flex items-center justify-center p-3 rounded-2xl bg-indigo-500/10 mb-6 ring-1 ring-indigo-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-indigo-400" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    <path d="M8 11h8" />
                    <path d="M12 7v8" />
                </svg>
            </div>
            <h1
                class="text-4xl md:text-5xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-cyan-400 mb-4">
                Healthcare Portal API
            </h1>
            <p class="text-slate-400 text-lg">System Status & Health Monitor</p>
        </div>

        <!-- Status Cards -->
        <div class="grid md:grid-cols-2 gap-6 mb-12">
            <!-- System Status -->
            <div class="glass rounded-2xl p-6 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="h-24 w-24" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse-slow"></span>
                    System Health
                </h2>

                <div class="space-y-4">
                    <!-- Database Check -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-800/50">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 7v10c0 2.21 3.58 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.58 4 8 4s8-1.79 8-4M4 7c0-2.21 3.58-4 8-4s8 1.79 8 4m0 5c0 2.21-3.58 4-8 4s-8-1.79-8-4" />
                            </svg>
                            <span>Database Connection</span>
                        </div>
                        <?php
try {
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 ring-1 ring-emerald-500/20">Connected</span>';
} catch (\Exception $e) {
    echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 ring-1 ring-rose-500/20">Error</span>';
}
                        ?>
                    </div>

                    <!-- Environment -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-800/50">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            <span>Environment</span>
                        </div>
                        <span class="text-slate-300 font-mono text-sm">{{ app()->environment() }}</span>
                    </div>

                    <!-- Laravel Version -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-800/50">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                            <span>Laravel Version</span>
                        </div>
                        <span class="text-slate-300 font-mono text-sm v">{{ app()->version() }}</span>
                    </div>
                </div>
            </div>

            <!-- API Statistics -->
            <div class="glass rounded-2xl p-6 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="h-24 w-24" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse-slow"></span>
                    API Endpoints
                </h2>

                <div class="space-y-2">
                    <div
                        class="flex items-center justify-between p-2 hover:bg-slate-800/50 rounded-lg transition-colors cursor-default">
                        <span class="text-sm text-slate-400">Authentication</span>
                        <span
                            class="text-xs font-mono bg-indigo-500/10 text-indigo-400 px-2 py-1 rounded">/api/auth/*</span>
                    </div>
                    <div
                        class="flex items-center justify-between p-2 hover:bg-slate-800/50 rounded-lg transition-colors cursor-default">
                        <span class="text-sm text-slate-400">Patients</span>
                        <span
                            class="text-xs font-mono bg-cyan-500/10 text-cyan-400 px-2 py-1 rounded">/api/patients</span>
                    </div>
                    <div
                        class="flex items-center justify-between p-2 hover:bg-slate-800/50 rounded-lg transition-colors cursor-default">
                        <span class="text-sm text-slate-400">Hospitals</span>
                        <span
                            class="text-xs font-mono bg-cyan-500/10 text-cyan-400 px-2 py-1 rounded">/api/hospitals</span>
                    </div>
                    <div
                        class="flex items-center justify-between p-2 hover:bg-slate-800/50 rounded-lg transition-colors cursor-default">
                        <span class="text-sm text-slate-400">Appointments</span>
                        <span
                            class="text-xs font-mono bg-cyan-500/10 text-cyan-400 px-2 py-1 rounded">/api/appointments</span>
                    </div>
                    <div
                        class="flex items-center justify-between p-2 hover:bg-slate-800/50 rounded-lg transition-colors cursor-default">
                        <span class="text-sm text-slate-400">AI Chat</span>
                        <span
                            class="text-xs font-mono bg-fuchsia-500/10 text-fuchsia-400 px-2 py-1 rounded">/api/ai/chat</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-slate-500 text-sm">
            <p>&copy; {{ date('Y') }} Healthcare Portal API. All systems operational.</p>
        </div>
    </div>

</body>

</html>