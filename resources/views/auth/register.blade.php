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
                        <i data-lucide="user" class="h-5 w-5 text-gray-500"></i>
                    </div>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Full Name" required
                        class="block w-full rounded-xl bg-gray-900 border border-gray-800 py-3.5 pl-12 pr-4 text-sm font-medium text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            {{-- IDENTIFIER (NIM / NIDN) --}}
            <div>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <i data-lucide="hash" class="h-5 w-5 text-gray-500"></i>
                    </div>
                    <input type="text" name="identifier" value="{{ old('identifier') }}" placeholder="NIM / NIDN" required
                        class="block w-full rounded-xl bg-gray-900 border border-gray-800 py-3.5 pl-12 pr-4 text-sm font-medium text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            {{-- EMAIL --}}
            <div>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <i data-lucide="mail" class="h-5 w-5 text-gray-500"></i>
                    </div>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Address" required
                        class="block w-full rounded-xl bg-gray-900 border border-gray-800 py-3.5 pl-12 pr-4 text-sm font-medium text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            {{-- PASSWORD --}}
            <div>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <i data-lucide="lock" class="h-5 w-5 text-gray-500"></i>
                    </div>
                    <input type="password" name="password" placeholder="Password" required
                        class="block w-full rounded-xl bg-gray-900 border border-gray-800 py-3.5 pl-12 pr-4 text-sm font-medium text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <button type="submit" class="w-full rounded-xl bg-blue-600 py-3.5 font-semibold text-white shadow-md shadow-blue-500/10 transition hover:bg-blue-700 active:scale-[0.98]">
                Register Account
            </button>
        </form>

        <div class="mt-6 text-center text-[10px] text-gray-600 flex justify-center gap-2 font-mono">
            <span class="inline-flex items-center gap-1"><i data-lucide="pen-tool" class="w-3 h-3"></i> biji kuda</span>
            <span>•</span>
            <span class="inline-flex items-center gap-1"><i data-lucide="message-square" class="w-3 h-3"></i> Tulis Komentar</span>
        </div>
    </div>
</x-auth-layout>