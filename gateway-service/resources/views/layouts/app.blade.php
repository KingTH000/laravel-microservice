<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Add this to send the CSRF token with AJAX requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Microservice App</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>

<body class="bg-gray-100 font-sans text-gray-900">
    <nav class="bg-white shadow-md">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-xl font-bold text-indigo-600">My App</span>
                    </div>
                </div>
                <div class="flex items-center">
                    @if(session('api_token'))
                        <a href="/profile"
                            class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Profile</a>
                        <form action="/logout" method="POST">
                            @csrf
                            <button type="submit"
                                class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Logout</button>
                        </form>
                    @else
                        <a href="/login"
                            class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                        <a href="/register"
                            class="ml-4 text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-2 rounded-md text-sm font-medium">Register</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <div class="min-h-screen flex flex-col items-center pt-10">
        <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg">
            <!-- This is where our page content will go -->
            @yield('content')
        </div>
    </div>
</body>

</html>