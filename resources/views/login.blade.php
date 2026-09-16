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
        <p class="mb-6 text-sm text-gray-400">Sign in to your account</p>

        @if(session('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
                <i class="ph ph-warning mr-1"></i>{{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-600 text-sm">
                <i class="ph ph-check-circle mr-1"></i>{{ session('success') }}
            </div>
        @endif

        <form action="/login" method="POST">
            @csrf
            <input type="text" name="username" placeholder="Username or Email" value="{{ old('username') }}" required
                   class="w-full p-3 mb-3 border border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                   autofocus>

            <div id="password-field" class="mb-3">
                <input type="password" name="password" placeholder="Password"
                       class="w-full p-3 mb-1 border border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                @error('password')
                    <p class="text-red-500 text-sm text-left">{{ $message }}</p>
                @enderror
                @error('username')
                    <p class="text-red-500 text-sm text-left">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full p-3 text-white bg-blue-600 rounded-xl font-semibold hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                Sign In
            </button>

            <p class="mt-3 text-right">
                <a href="/forgot-password" class="text-xs text-gray-400 hover:text-gray-600 hover:underline">Forgot password?</a>
            </p>
        </form>

        <p class="mt-4 text-sm text-gray-500">
            Don't have an account?
            <a href="/register" class="text-blue-600 hover:underline font-medium">Create one</a>
        </p>

        <p class="mt-6 text-xs text-gray-300">
            <i class="ph ph-lock-simple mr-1"></i>Your data is private to your account
        </p>
    </div>

    <script>
        const usernameInput = document.querySelector('input[name="username"]');
        const passwordField = document.getElementById('password-field');
        const passwordInput = document.querySelector('input[name="password"]');
        const submitBtn = document.querySelector('button[type="submit"]');

        function togglePassword() {
            const name = usernameInput.value.trim();
            const isMama = name.toLowerCase() === 'mama';

            if (isMama) {
                passwordField.style.display = 'none';
                passwordInput.removeAttribute('required');
                submitBtn.textContent = 'Enter as Mama';
            } else if (name.length > 0) {
                passwordField.style.display = 'block';
                passwordInput.setAttribute('required', 'required');
                submitBtn.textContent = 'Sign In';
            } else {
                passwordField.style.display = 'block';
                passwordInput.setAttribute('required', 'required');
                submitBtn.textContent = 'Sign In';
            }
        }

        usernameInput.addEventListener('input', togglePassword);
        usernameInput.addEventListener('blur', togglePassword);

        // Run on load in case username is pre-filled
        togglePassword();
    </script>

</body>
</html>
