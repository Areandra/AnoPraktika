{{-- login.blade.php --}}
<x-auth-layout title="Sign In">
    <div class="mb-7">
        <h1 class="text-2xl font-bold text-white tracking-tight">Selamat Datang</h1>
        <p class="text-sm text-slate-400 mt-1">Masuk ke akun praktikum anda</p>
    </div>

    {{-- Tab Toggle --}}
    <div class="mb-7 flex rounded-xl bg-slate-900/80 border border-slate-800 p-1">
        <a href="{{ route('login') }}"
           class="flex-1 rounded-lg py-2.5 text-center text-sm font-semibold text-white transition"
           style="background: linear-gradient(135deg, rgba(99,102,241,0.2) 0%, rgba(59,130,246,0.15) 100%); border: 1px solid rgba(99,102,241,0.25);">
            Sign In
        </a>
        <a href="{{ route('register') }}"
           class="flex-1 rounded-lg py-2.5 text-center text-sm font-medium text-slate-400 transition hover:text-white">
            Sign Up
        </a>
    </div>

    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf

        {{-- EMAIL --}}
        <div>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-500"></i>
                </div>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Address"
                    class="input-field block w-full rounded-xl py-3.5 pl-10 pr-4 text-sm @error('email') border-red-500/50 @enderror">
            </div>
            @error('email')
                <p class="mt-1.5 text-xs text-red-400 font-mono flex items-center gap-1">
                    <i data-lucide="alert-circle" class="w-3 h-3"></i> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- PASSWORD --}}
        <div x-data="{ showPassword: false }">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <i data-lucide="lock" class="w-4 h-4 text-slate-500"></i>
                </div>
                <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Password" required
                    class="input-field block w-full rounded-xl py-3.5 pl-10 pr-11 text-sm @error('password') border-red-500/50 @enderror">
                <button type="button" @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-500 hover:text-slate-300 transition-colors">
                    <i x-show="showPassword" data-lucide="eye" class="w-4 h-4"></i>
                    <i x-show="!showPassword" data-lucide="eye-off" class="w-4 h-4" x-cloak></i>
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-xs text-red-400 font-mono flex items-center gap-1">
                    <i data-lucide="alert-circle" class="w-3 h-3"></i> {{ $message }}
                </p>
            @enderror
        </div>

        <button type="submit" class="btn-primary w-full rounded-xl py-3.5 text-sm font-semibold text-white mt-2">
            Masuk ke Workspace
        </button>
    </form>
</x-auth-layout>