{{-- register.blade.php --}}
<x-auth-layout title="Sign Up">
    <div class="bg-[#111827] border border-gray-800 p-8 rounded-2xl shadow-xl max-w-sm mx-auto">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-white tracking-tight">Create Account</h1>
            <p class="text-xs text-gray-400 mt-1">Daftar akun praktikum baru anda</p>
        </div>

        {{-- Toggle Sign In / Sign Up bertema Gelap --}}
        <div class="mb-7 flex rounded-xl bg-gray-900 border border-gray-800 p-1">
            <a href="{{ route('login') }}" class="flex-1 rounded-lg py-2.5 text-center text-sm font-medium text-gray-400 transition hover:text-white">
                Sign In
            </a>
            <a href="{{ route('register') }}" class="flex-1 rounded-lg bg-gray-800 py-2.5 text-center text-sm font-semibold text-white shadow-sm border border-gray-700/50 transition">
                Signup
            </a>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-5">
            @csrf
            {{-- NAMA LENGKAP --}}
            <div>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <i class="ri-user-line text-lg text-gray-500"></i>
                    </div>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Full Name" required
                        class="block w-full rounded-xl bg-gray-900 border border-gray-800 py-3.5 pl-12 pr-4 text-sm font-medium text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                @error('name')<p class="mt-2 text-xs text-red-400 font-mono">{{ $message }}</p>@enderror
            </div>

            {{-- IDENTIFIER (NIM / NIDN) --}}
            <div>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <i class="ri-hashtag text-lg text-gray-500"></i>
                    </div>
                    <input type="text" name="identifier" value="{{ old('identifier') }}" placeholder="NIM / NIDN" required
                        class="block w-full rounded-xl bg-gray-900 border border-gray-800 py-3.5 pl-12 pr-4 text-sm font-medium text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                @error('identifier')<p class="mt-2 text-xs text-red-400 font-mono">{{ $message }}</p>@enderror
            </div>

            {{-- EMAIL --}}
            <div>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <i class="ri-mail-line text-lg text-gray-500"></i>
                    </div>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Address" required
                        class="block w-full rounded-xl bg-gray-900 border border-gray-800 py-3.5 pl-12 pr-4 text-sm font-medium text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                @error('email')<p class="mt-2 text-xs text-red-400 font-mono">{{ $message }}</p>@enderror
            </div>

            {{-- PASSWORD UTAMA --}}
            <div x-data="{ showPassword: false }">
                <div class="relative">
                    {{-- Ikon Gembok Kiri --}}
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <i class="ri-lock-line text-lg text-gray-500"></i>
                    </div>
                    
                    {{-- Input Field --}}
                    <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Password" required
                        class="block w-full rounded-xl bg-gray-900 border border-gray-800 py-3.5 pl-12 pr-12 text-sm font-medium text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('password') border-red-500/50 @enderror">
                    
                    {{-- Tombol Mata Kanan --}}
                    <button type="button" @click="showPassword = !showPassword" 
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-white transition-colors">
                        <i x-show="showPassword" class="ri-eye-line text-lg"></i>
                        <i x-show="!showPassword" class="ri-eye-off-line text-lg" x-cloak></i>
                    </button>
                </div>
                @error('password')<p class="mt-2 text-xs text-red-400 font-mono">{{ $message }}</p>@enderror
            </div>

            {{-- PASSWORD CONFIRMATION FIELD --}}
            <div x-data="{ showConfirm: false }">
                <div class="relative">
                   {{-- Ikon Gembok Kiri --}}
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <i class="ri-lock-line text-lg text-gray-500"></i>
                    </div>
                    
                    {{-- Input Field --}}
                    <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" placeholder="Confirm Password" required
                        class="block w-full rounded-xl bg-gray-900 border border-gray-800 py-3.5 pl-12 pr-12 text-sm font-medium text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    
                    {{-- Tombol Mata Kanan --}}
                    <button type="button" @click="showConfirm = !showConfirm" 
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-white transition-colors">
                        <i x-show="showConfirm" class="ri-eye-line text-lg"></i>
                        <i x-show="!showConfirm" class="ri-eye-off-line text-lg" x-cloak></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="w-full rounded-xl bg-blue-600 py-3.5 font-semibold text-white shadow-md shadow-blue-500/10 transition hover:bg-blue-700 active:scale-[0.98]">
                Register Account
            </button>
        </form>
    </div>
</x-auth-layout>