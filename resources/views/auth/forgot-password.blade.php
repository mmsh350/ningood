@extends('layouts.auth')

@section('title', 'Forget Password')

@section('content')
    <div class="bg-white p-8 rounded-[2rem] shadow-2xl shadow-slate-100 border border-slate-50">
        <div class="flex flex-col items-center mb-10">
            <a href="/" class="flex flex-col items-center gap-3 group mb-4">
                <div class="w-12 h-12 bg-primary-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-primary-200 group-hover:bg-primary-700 transition">
                    <i class="bi bi-shield-check text-2xl"></i>
                </div>
                <span class="text-2xl font-bold tracking-tight text-slate-900">Ningood</span>
            </a>
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Reset Password</h1>
            <p class="text-slate-500 italic">Enter your email to receive a password reset link</p>
        </div>

        @include('common.message')

        <form method="POST" action="{{ route('auth.password.email') }}" class="space-y-6">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-bold text-slate-700 mb-2">E-Mail Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <i class="bi bi-envelope text-lg"></i>
                    </span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" 
                        placeholder="john@example.com"
                        class="form-input pl-11 @error('email') border-red-500 @enderror" 
                        required autofocus>
                </div>
                @error('email')
                    <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full py-4 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 shadow-lg shadow-primary-200 transition transform active:scale-[0.98]">
                Send Reset Link
            </button>

            <div class="text-center pt-4">
                <a href="{{ route('auth.login') }}" class="text-sm font-bold text-primary-600 hover:text-primary-700 transition flex items-center justify-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    Back to Login
                </a>
            </div>
        </form>
    </div>
@endsection
