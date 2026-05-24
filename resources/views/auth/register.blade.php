<x-auth-layout title="Sign Up">
    <div class="mb-8 text-center">
        <h1 class="mb-2 text-3xl font-bold text-gray-900">Create Account</h1>
        <p class="text-sm text-gray-400">
            Join us by filling in your academic details
        </p>
    </div>

    <div class="mb-8 flex rounded-xl bg-gray-100 p-1">
        <a href="{{ route('login') }}"
            class="flex-1 rounded-lg py-2.5 text-center text-sm font-medium text-gray-400 transition hover:text-gray-700">
            Sign In
        </a>

        <a href="{{ route('register') }}"
            class="flex-1 rounded-lg bg-white py-2.5 text-center text-sm font-semibold text-gray-900 shadow-sm">
            Signup
        </a>
    </div>

    <form action="{{ route('register') }}" method="POST" class="space-y-5">
        @csrf

        {{-- EMAIL --}}
        <div>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <i data-lucide="mail" class="h-5 w-5 text-gray-400"></i>
                </div>

                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Address"
                    class="block w-full rounded-xl border py-3.5 pl-12 text-sm focus:border-blue-500 focus:ring-blue-500
                    @error('email')
                        border-red-500
                    @else
                        border-gray-200
                    @enderror">
            </div>

            @error('email')
                <p class="mt-2 text-sm text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- IDENTIFIER --}}
        <div>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <i data-lucide="user" class="h-5 w-5 text-gray-400"></i>
                </div>

                <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama"
                    class="block w-full rounded-xl border py-3.5 pl-12 text-sm focus:border-blue-500 focus:ring-blue-500
                    @error('name')
                        border-red-500
                    @else
                        border-gray-200
                    @enderror">
            </div>

            @error('name')
                <p class="mt-2 text-sm text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <i data-lucide="badge-check" class="h-5 w-5 text-gray-400"></i>
                </div>

                <input type="text" name="identifier" value="{{ old('identifier') }}" placeholder="NIM / NIP"
                    class="block w-full rounded-xl border py-3.5 pl-12 text-sm focus:border-blue-500 focus:ring-blue-500
                    @error('identifier')
                        border-red-500
                    @else
                        border-gray-200
                    @enderror">
            </div>

            @error('identifier')
                <p class="mt-2 text-sm text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- PASSWORD --}}
        <div x-data="{ showPassword: false }">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <i data-lucide="lock" class="h-5 w-5 text-gray-400"></i>
                </div>

                <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Password"
                    class="block w-full rounded-xl border py-3.5 pl-12 pr-12 text-sm focus:border-blue-500 focus:ring-blue-500
                    @error('password')
                        border-red-500
                    @else
                        border-gray-200
                    @enderror">

                <button type="button" @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-700">

                    <i x-show="!showPassword" data-lucide="eye" class="h-5 w-5"></i>

                    <i x-show="showPassword" data-lucide="eye-off" class="h-5 w-5"></i>
                </button>
            </div>

            @error('password')
                <p class="mt-2 text-sm text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- PASSWORD CONFIRMATION --}}
        <div x-data="{ showPassword: false }">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <i data-lucide="shield-check" class="h-5 w-5 text-gray-400"></i>
                </div>

                <input :type="showPassword ? 'text' : 'password'" name="password_confirmation"
                    placeholder="Confirm Password"
                    class="block w-full rounded-xl border border-gray-200 py-3.5 pl-12 pr-12 text-sm focus:border-blue-500 focus:ring-blue-500">

                <button type="button" @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-700">

                    <i x-show="!showPassword" data-lucide="eye" class="h-5 w-5"></i>

                    <i x-show="showPassword" data-lucide="eye-off" class="h-5 w-5"></i>
                </button>
            </div>
        </div>

        <button type="submit"
            class="mt-2 w-full rounded-xl bg-[#0057FF] py-3.5 font-medium text-white shadow-lg shadow-blue-500/30 transition-colors hover:bg-blue-700">
            Create Account
        </button>
    </form>
</x-auth-layout>
