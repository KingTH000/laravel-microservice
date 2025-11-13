@extends('layouts.app')

@section('content')
    <h2 class="text-2xl font-bold text-center mb-6">Welcome Back!</h2>

    <!-- Show general errors -->
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="/login" method="POST">
        @csrf

        <!-- Email Field -->
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
            <input type="email" name="email" id="email"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                value="{{ old('email') }}" required>
        </div>

        <!-- Password Field -->
        <div class="mb-6">
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" id="password"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                required>
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Login
            </button>
        </div>

        <p class="text-center text-sm text-gray-600 mt-4">
            Don't have an account?
            <a href="/register" class="font-medium text-indigo-600 hover:text-indigo-500">
                Register here
            </a>
        </p>
    </form>
@endsection