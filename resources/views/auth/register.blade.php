@extends('layouts.auth')

@section('title', 'Register')

@section('content')
    <div class="bg-white p-8 rounded-[2rem] shadow-2xl shadow-slate-100 border border-slate-50">
        <div class="flex flex-col items-center mb-10">
            <a href="/" class="flex flex-col items-center gap-3 group mb-4">
                <div class="w-12 h-12 bg-primary-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-primary-200 group-hover:bg-primary-700 transition">
                    <i class="bi bi-shield-check text-2xl"></i>
                </div>
                <span class="text-2xl font-bold tracking-tight text-slate-900">Ningood</span>
            </a>
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Join Ningood</h1>
            <p class="text-slate-500">Create your account in just a few steps</p>
        </div>

        @include('common.message')

        <form method="POST" action="{{ route('auth.register') }}" class="space-y-5">
            @csrf

            <!-- Full Name -->
            <div>
                <label for="name" class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <i class="bi bi-person text-lg"></i>
                    </span>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" 
                        placeholder="John Doe"
                        class="form-input pl-11 @error('name') border-red-500 @enderror" 
                        required>
                </div>
                @error('name')
                    <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <i class="bi bi-envelope text-lg"></i>
                    </span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" 
                        placeholder="john@example.com"
                        class="form-input pl-11 @error('email') border-red-500 @enderror" 
                        required>
                </div>
                @error('email')
                    <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone Number -->
            <div>
                <label for="phone_number" class="block text-sm font-bold text-slate-700 mb-2">Phone Number</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <i class="bi bi-phone text-lg"></i>
                    </span>
                    <input type="tel" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" 
                        placeholder="08012345678" maxlength="11"
                        class="form-input pl-11 @error('phone_number') border-red-500 @enderror" 
                        required>
                </div>
                @error('phone_number')
                    <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Referral Code -->
            <div>
                <label for="referral_code" class="block text-sm font-bold text-slate-700 mb-2">Referral Code (Optional)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <i class="bi bi-tag text-lg"></i>
                    </span>
                    <input type="text" id="referral_code" name="referral_code" value="{{ old('referral_code') }}" 
                        placeholder="Enter code" maxlength="6"
                        class="form-input pl-11 @error('referral_code') border-red-500 @enderror">
                </div>
                @error('referral_code')
                    <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Passwords Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="password" class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" 
                        placeholder="••••••••"
                        class="form-input @error('password') border-red-500 @enderror" 
                        required>
                    @error('password')
                        <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-slate-700 mb-2">Confirm</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" 
                        placeholder="••••••••"
                        class="form-input" 
                        required>
                </div>
            </div>

            <div class="flex items-start">
                <input type="checkbox" id="terms" name="terms" value="1" {{ old('terms') ? 'checked' : '' }}
                    class="mt-1 w-4 h-4 text-primary-600 bg-slate-50 border-slate-200 rounded focus:ring-primary-500">
                <label for="terms" class="ml-2 text-sm font-medium text-slate-500 leading-tight">
                    I agree to the <a href="#" class="text-primary-600 font-bold hover:underline">Terms of Service</a> and <a href="#" class="text-primary-600 font-bold hover:underline">Privacy Policy</a>
                </label>
            </div>
            @error('terms')
                <p class="text-xs text-red-500 font-medium">{{ $message }}</p>
            @enderror

            <button type="submit" class="w-full py-4 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 shadow-lg shadow-primary-200 transition transform active:scale-[0.98]">
                Create My Account
            </button>

            <div class="text-center pt-4">
                <p class="text-sm text-slate-500">
                    Already have an account? 
                    <a href="{{ route('auth.login') }}" class="font-bold text-primary-600 hover:text-primary-700 transition">Login Here</a>
                </p>
            </div>
        </form>
    </div>
@endsection
