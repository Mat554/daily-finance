<!DOCTYPE html>
<html>
<head>
    <title>Create Account — Daily Finance</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex items-center justify-center h-screen" style="background: linear-gradient(135deg, #f0f4ff 0%, #fdf4ff 100%);">

    <div class="p-8 bg-white rounded-2xl shadow-xl text-center max-w-sm w-full mx-4 border border-gray-100">
        <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-200">
            <i class="ph ph-chart-line-up text-white text-2xl"></i>
        </div>
        <h2 class="mb-1 text-2xl font-bold text-gray-900">Create Account</h2>
        <p class="mb-6 text-sm text-gray-400">Start tracking your finances</p>

        @if(session('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm text-left">
                <i class="ph ph-warning mr-1"></i>{{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm text-left">
                @foreach($errors->all() as $error)
                    <div><i class="ph ph-warning mr-1"></i>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="/register" method="POST">
            @csrf
            <input type="text" name="username" placeholder="Choose a username" value="{{ old('username') }}" required
                   class="w-full p-3 mb-3 border border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                   autofocus>
            <input type="email" name="email" placeholder="Email address" value="{{ old('email') }}" required
                   class="w-full p-3 mb-3 border border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            <input type="password" name="password" placeholder="Choose a password (min 6 characters)" required
                   class="w-full p-3 mb-3 border border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            <input type="password" name="password_confirmation" placeholder="Confirm your password" required
                   class="w-full p-3 mb-4 border border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">

            <button type="submit" class="w-full p-3 text-white bg-blue-600 rounded-xl font-semibold hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                Create Account
            </button>
        </form>

        <p class="mt-4 text-sm text-gray-500">
            Already have an account?
            <a href="/login" class="text-blue-600 hover:underline font-medium">Sign in</a>
        </p>
    </div>

</body>
</html>
