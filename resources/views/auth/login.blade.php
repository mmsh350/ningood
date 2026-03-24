@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="bg-white p-8 rounded-[2rem] shadow-2xl shadow-slate-100 border border-slate-50 relative">
        <div class="flex flex-col items-center mb-10">
            <a href="/" class="flex flex-col items-center gap-3 group mb-4">
                <div class="w-12 h-12 bg-primary-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-primary-200 group-hover:bg-primary-700 transition">
                    <i class="bi bi-shield-check text-2xl"></i>
                </div>
                <span class="text-2xl font-bold tracking-tight text-slate-900">Ningood</span>
            </a>
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Welcome Back</h1>
            <p class="text-slate-500">Sign in to continue to your dashboard</p>
        </div>

        @include('common.message')

        <form method="POST" action="{{ route('auth.login') }}" class="space-y-6">
            @csrf

            <!-- Username/Email -->
            <div>
                <label for="email" class="block text-sm font-bold text-slate-700 mb-2">Username or Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <i class="bi bi-person text-lg"></i>
                    </span>
                    <input type="text" id="email" name="email" value="{{ old('email') }}" 
                        placeholder="Enter your username"
                        class="form-input pl-11 @error('email') border-red-500 @enderror" 
                        required autofocus>
                </div>
                @error('email')
                    <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <div class="flex justify-between mb-2">
                    <label for="password" class="text-sm font-bold text-slate-700">Password</label>
                    <a href="{{ route('auth.password.request') }}" class="text-xs font-bold text-primary-600 hover:text-primary-700 transition">Forgot password?</a>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <i class="bi bi-lock text-lg"></i>
                    </span>
                    <input type="password" id="password" name="password" 
                        placeholder="••••••••"
                        class="form-input pl-11 @error('password') border-red-500 @enderror" 
                        required>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}
                    class="w-4 h-4 text-primary-600 bg-slate-50 border-slate-200 rounded focus:ring-primary-500">
                <label for="remember" class="ml-2 text-sm font-medium text-slate-500 cursor-pointer">Keep me signed in</label>
            </div>

            <button type="submit" class="w-full py-4 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 shadow-lg shadow-primary-200 transition transform active:scale-[0.98]">
                Login to Account
            </button>

            <div class="text-center pt-4">
                <p class="text-sm text-slate-500">
                    Don't have an account? 
                    <a href="{{ route('auth.register') }}" class="font-bold text-primary-600 hover:text-primary-700 transition">Create Account</a>
                </p>
            </div>
        </form>
    </div>
@endsection
