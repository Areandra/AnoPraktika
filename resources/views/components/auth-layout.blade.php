<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Authentication' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="max-w-6xl w-full bg-white rounded-3xl shadow-xl overflow-hidden flex flex-col md:flex-row min-h-[700px]">

        <div class="w-full md:w-1/2 p-8 md:p-16 flex flex-col justify-between">

            <div class="w-full max-w-sm m-auto ">
                @error('custom_message')
                    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $message }}
                    </div>
                @enderror
                @if (session('success'))
                    <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ session('success') }}
                    </div>
                @endif
                {{ $slot }}
            </div>


        </div>

        <div
            class="hidden md:flex w-1/2 bg-gradient-to-b from-blue-50 to-blue-300 items-center justify-center relative overflow-hidden">
            <div class="absolute p-8 bottom-0 flex items-center gap-2 font-bold text-xl text-gray-900">
                AnoPraktika
                <svg class="w-7 h-7 text-black" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 12l4-8h8l4 8-4 8H8l-4-8z"></path>
                </svg>
            </div>
            <div
                class="absolute inset-0 opacity-30 bg-[linear-gradient(to_bottom,transparent_40%,white_100%),repeating-linear-gradient(to_right,transparent,transparent_40px,white_40px,white_42px)]">
            </div>

            <div
                class="relative z-10 w-64 h-64 bg-blue-600 rounded-3xl shadow-2xl rotate-12 flex items-center justify-center border-[12px] border-blue-400">
                <div
                    class="w-24 h-24 rounded-full bg-teal-300 border-[10px] border-teal-400 flex items-center justify-center shadow-inner">
                    <div class="w-6 h-6 rounded-full bg-gray-800"></div>
                </div>
                <div
                    class="absolute w-1 h-16 bg-gray-300 rounded-full left-1/2 top-1/2 -translate-y-1/2 ml-14 rotate-45 shadow-md">
                </div>
                <div
                    class="absolute w-1 h-16 bg-gray-300 rounded-full left-1/2 top-1/2 -translate-y-1/2 ml-14 -rotate-45 shadow-md">
                </div>
            </div>
        </div>

    </div>
</body>

</html>
