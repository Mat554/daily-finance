<!DOCTYPE html>
<html>
<head>
    <title>Daily Finance Tracker</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex items-center justify-center h-screen" style="background: linear-gradient(135deg, #f0f4ff 0%, #fdf4ff 100%);">

    <div class="p-8 bg-white rounded-2xl shadow-xl text-center max-w-sm w-full mx-4 border border-gray-100">
        <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-200">
            <i class="ph ph-chart-line-up text-white text-2xl"></i>
        </div>
        <h2 class="mb-1 text-2xl font-bold text-gray-900">Daily Finance</h2>
        <p class="mb-6 text-sm text-gray-400">Enter your name to start tracking</p>

        <form action="/login" method="POST">
            @csrf
            <input type="text" name="username" placeholder="Your name..." required
                   class="w-full p-3 mb-3 border border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                   autofocus>
            <button type="submit" class="w-full p-3 text-white bg-blue-600 rounded-xl font-semibold hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                Start Tracking
            </button>
        </form>

        <p class="mt-6 text-xs text-gray-300">
            <i class="ph ph-lock-simple mr-1"></i>Your data is private to your session
        </p>
    </div>

</body>
</html>