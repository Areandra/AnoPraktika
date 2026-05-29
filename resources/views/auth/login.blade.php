{{-- login.blade.php --}}
<x-auth-layout title="Sign In">
    {{-- Container pembungkus bertema Dark Mode agar serasi dengan dashboard --}}
    <div class="bg-[#111827] border border-gray-800 p-8 rounded-2xl shadow-xl max-w-sm mx-auto">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-white tracking-tight">Welcome Back</h1>
            <p class="text-xs text-gray-400 mt-1">Masuk ke akun praktikum anda</p>
        </div>

        {{-- Toggle Sign In / Sign Up bertema Gelap --}}
        <div class="mb-7 flex rounded-xl bg-gray-900 border border-gray-800 p-1">
            <a href="{{ route('login') }}" class="flex-1 rounded-lg bg-gray-800 py-2.5 text-center text-sm font-semibold text-white shadow-sm border border-gray-700/50 transition">
                Sign In
            </a>
            <a href="{{ route('register') }}" class="flex-1 rounded-lg py-2.5 text-center text-sm font-medium text-gray-400 transition hover:text-white">
                Signup
            </a>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf
            {{-- EMAIL --}}
            <div>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <i data-lucide="mail" class="h-5 w-5 text-gray-500"></i>
                    </div>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Address"
                        class="block w-full rounded-xl bg-gray-900 border py-3.5 pl-12 pr-4 text-sm font-medium text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('email') border-red-500/50 @else border-gray-800 @enderror">
                    @error('email')
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                            <i data-lucide="alert-circle" class="h-5 w-5 text-red-500"></i>
                        </div>
                    @enderror
                </div>
                @error('email')<p class="mt-2 text-xs text-red-400 font-mono">{{ $message }}</p>@enderror
            </div>

            {{-- PASSWORD --}}
            <div x-data="{ showPassword: false }">
                <div class="relative">
                    {{-- Ikon Gembok di Sisi Kiri --}}
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <i class="ri-lock-line text-lg text-gray-500"></i>
                    </div>
                    
                    {{-- Input Password yang T tipenya Berubah Dinamis via Alpine.js --}}
                    <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Password" required
                        class="block w-full rounded-xl bg-gray-900 border border-gray-800 py-3.5 pl-12 pr-12 text-sm font-medium text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('password') border-red-500/50 @enderror">
                    
                    {{-- Tombol Mata Pengubah State (Sisi Kanan) --}}
                    <button type="button" @click="showPassword = !showPassword" 
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-white transition-colors">
                        
                        {{-- Tampilkan ikon mata terbuka jika password sedang tersembunyi --}}
                        <i x-show="showPassword" class="ri-eye-line text-lg"></i>
                        
                        {{-- Tampilkan ikon mata dicoret jika password sedang terlihat --}}
                        <i x-show="!showPassword" class="ri-eye-off-line text-lg" x-cloak></i>
                        
                    </button>
                </div>
                @error('password')<p class="mt-2 text-xs text-red-400 font-mono">{{ $message }}</p>@enderror
            </div>

            {{-- Tombol Utama dengan Efek Glow Khas Dashboard --}}
            <button type="submit" class="w-full rounded-xl bg-blue-600 py-3.5 font-semibold text-white shadow-md shadow-blue-500/10 transition hover:bg-blue-700 active:scale-[0.98]">
                Continue
            </button>
        </form>
    </div>
</x-auth-layout>