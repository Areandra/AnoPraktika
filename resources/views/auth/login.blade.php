{{-- login.blade.php --}}
<x-auth-layout title="Sign In">
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Welcome Back</h1>
        <p class="text-sm text-gray-400 mt-1">Masuk ke akun praktikum anda</p>
    </div>

    {{-- toggle sign in / sign up -- mengikuti desain card bawaan --}}
    <div class="mb-7 flex rounded-xl bg-gray-100 p-1">
        <a href="{{ route('login') }}" class="flex-1 rounded-lg bg-white py-2.5 text-center text-sm font-semibold text-gray-900 shadow-sm transition">
            Sign In
        </a>
        <a href="{{ route('register') }}" class="flex-1 rounded-lg py-2.5 text-center text-sm font-medium text-gray-400 transition hover:text-gray-700">
            Signup
        </a>
    </div>

    <form action="{{ route('login') }}" method="POST" class="space-y-5">
        @csrf
        {{-- EMAIL --}}
        <div>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <i data-lucide="mail" class="h-5 w-5 text-gray-400"></i>
                </div>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Address"
                    class="block w-full rounded-xl border py-3.5 pl-12 pr-4 text-sm font-medium text-gray-800 focus:border-[#0057FF] focus:ring-1 focus:ring-[#0057FF] @error('email') border-red-500 @else border-gray-200 @enderror">
                @error('email')
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                        <i data-lucide="alert-circle" class="h-5 w-5 text-red-500"></i>
                    </div>
                @enderror
            </div>
            @error('email')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- PASSWORD --}}
        <div x-data="{ showPassword: false }">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <i data-lucide="lock" class="h-5 w-5 text-gray-400"></i>
                </div>
                <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Password"
                    class="block w-full rounded-xl border py-3.5 pl-12 pr-12 text-sm font-medium text-gray-800 focus:border-[#0057FF] focus:ring-1 focus:ring-[#0057FF] @error('password') border-red-500 @else border-gray-200 @enderror">
                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-700">
                    <i x-show="!showPassword" data-lucide="eye" class="h-5 w-5"></i>
                    <i x-show="showPassword" data-lucide="eye-off" class="h-5 w-5"></i>
                </button>
            </div>
            @error('password')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="w-full rounded-xl bg-[#0057FF] py-3.5 font-semibold text-white shadow-md shadow-blue-500/20 transition hover:bg-blue-700">
            Continue
        </button>
    </form>

    {{-- tambahan referensi tema: "biji kuda" dan "Tulis Komentar" sebagai sentuhan --}}
    <div class="mt-5 text-center text-[11px] text-gray-300 flex justify-center gap-2">
        <span class="inline-flex items-center gap-1"><i data-lucide="pen-tool" class="w-3 h-3"></i> biji kuda</span>
        <span>•</span>
        <span class="inline-flex items-center gap-1"><i data-lucide="message-square" class="w-3 h-3"></i> Tulis Komentar: Halalan Ini</span>
    </div>
</x-auth-layout>
