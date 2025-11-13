@extends('layouts.app')

@section('content')
    <h2 class="text-2xl font-bold text-center mb-6">Your Profile</h2>

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

    <!-- Show success message -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <form action="/profile" method="POST">
        @csrf

        <!-- First Name -->
        <div class="mb-4">
            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
            <input type="text" name="first_name" id="first_name"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                value="{{ old('first_name', $profile['first_name']) }}">
        </div>

        <!-- Last Name -->
        <div class="mb-4">
            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
            <input type="text" name="last_name" id="last_name"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                value="{{ old('last_name', $profile['last_name']) }}">
        </div>


        <!-- Bio -->
        <div class="mt-6">
            <label for="bio" class="block text-sm font-medium text-gray-700">Your Bio</label>
            <textarea name="bio" id="bio" rows="4"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('bio', $profile['bio']) }}</textarea>
        </div>

        <!-- Submit Button -->
        <div class="mt-6">
            <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Save Profile
            </button>
        </div>
    </form>

@endsection