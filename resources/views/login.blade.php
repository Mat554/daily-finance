<!DOCTYPE html>
<html>
<head>
    <title>Who is tracking?</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">

    <div class="p-8 bg-white rounded shadow-md text-center">
        <h2 class="mb-4 text-2xl font-bold">Daily Finance Tracker</h2>
        
        <form action="/login" method="POST">
            @csrf
            <input type="text" name="username" placeholder="Enter your name..." required 
                   class="w-full p-2 mb-4 border rounded">
            <button type="submit" class="w-full p-2 text-white bg-blue-500 rounded">
                Start Tracking
            </button>
        </form>
    </div>

</body>
</html>