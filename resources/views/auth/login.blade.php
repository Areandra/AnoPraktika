<x-auth-layout title="Sign In">
    <div class="mb-8 text-center">
        <h1 class="mb-2 text-3xl font-bold text-gray-900">Welcome Back</h1>
        <p class="text-sm text-gray-400">
            Welcome Back, Please enter Your details
        </p>
    </div>

    <div class="mb-8 flex rounded-xl bg-gray-100 p-1">
        <a href="{{ route('login') }}"
            class="flex-1 rounded-lg bg-white py-2.5 text-center text-sm font-semibold text-gray-900 shadow-sm">
            Sign In
        </a>

        <a href="{{ route('register') }}"
            class="flex-1 rounded-lg py-2.5 text-center text-sm font-medium text-gray-400 transition hover:text-gray-700">
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
                    class="block w-full rounded-xl border py-3.5 pl-12 pr-10 text-sm font-medium text-gray-800 focus:border-blue-500 focus:ring-blue-500
                    @error('email')
                        border-red-500
                    @else
                        border-gray-200
                    @enderror">

                @error('email')
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10A8 8 0 114 4a8 8 0 0114 6zm-7-4a1 1 0 00-2 0v5a1 1 0 002 0V6zm0 8a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                @enderror
            </div>

            @error('email')
                <p class="mt-2 text-sm text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- PASSWORD --}}
        <div x-data="{ showPassword: false }">
            <div class="relative">
                {{-- ICON --}}
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <i data-lucide="lock" class="h-5 w-5 text-gray-400"></i>
                </div>

                <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Password"
                    class="block w-full rounded-xl border py-3.5 pl-12 pr-12 text-sm font-medium text-gray-800 focus:border-blue-500 focus:ring-blue-500
            @error('password')
                border-red-500
            @else
                border-gray-200
            @enderror">

                {{-- TOGGLE PASSWORD --}}
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

        <button type="submit"
            class="w-full rounded-xl bg-[#0057FF] py-3.5 font-medium text-white shadow-lg shadow-blue-500/30 transition-colors hover:bg-blue-700">
            Continue
        </button>
    </form>
</x-auth-layout>
