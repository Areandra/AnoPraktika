@props([
    'filePath' => null,
    'fileName' => null,
])

@php
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    $codeExtensions = [
        'js',
        'ts',
        'jsx',
        'tsx',
        'php',
        'py',
        'java',
        'cpp',
        'c',
        'cs',
        'go',
        'rs',
        'rb',
        'kt',
        'swift',
        'html',
        'css',
        'scss',
        'json',
        'xml',
        'md',
        'txt',
        'sql',
        'sh',
    ];

    $isCode = in_array($extension, $codeExtensions);
    $isZip = in_array($extension, ['zip', 'rar', '7z']);

    $storagePath = storage_path('app/public/' . $filePath);

    $content = null;

    if ($isCode && file_exists($storagePath)) {
        $content = file_get_contents($storagePath);
    }
@endphp

<div class="flex-1 overflow-auto bg-[#0f172a] text-white p-4">

    {{-- ZIP / Archive --}}
    @if ($isZip)
        <div class="max-w-xl mx-auto bg-[#111827] border border-gray-800 rounded-2xl p-6 space-y-4 shadow-2xl">

            <div>
                <h2 class="text-lg font-bold text-white">
                    File Arsip Tugas
                </h2>

                <p class="text-sm text-gray-400 mt-1">
                    File tidak dapat dipreview langsung.
                </p>
            </div>

            <div class="bg-[#0f172a] border border-gray-700 rounded-xl p-3 text-sm font-mono text-gray-300">
                {{ $fileName }}
            </div>

            <a href="{{ asset('storage/' . $filePath) }}" download
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-white font-semibold transition">
                <i class="ri-download-line"></i>
                Download File
            </a>
        </div>

        {{-- CODE / MARKDOWN --}}
    @elseif ($isCode)
        <div class="h-full flex flex-col">

            <div class="flex items-center justify-between border-b border-gray-800 pb-3 mb-3">
                <div>
                    <h2 class="font-bold text-white">
                        {{ $fileName }}
                    </h2>

                    <p class="text-xs text-gray-500 uppercase">
                        {{ $extension }} file
                    </p>
                </div>

                <a href="{{ asset('storage/' . $filePath) }}" download
                    class="px-3 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm font-semibold">
                    Download
                </a>
            </div>

            <pre class="flex-1 overflow-auto bg-[#020617] border border-gray-800 rounded-2xl p-4 text-sm leading-6"><code>{{ $content }}</code></pre>
        </div>

        {{-- UNKNOWN FILE --}}
    @else
        <div class="max-w-xl mx-auto bg-[#111827] border border-gray-800 rounded-2xl p-6 space-y-4 shadow-2xl">

            <div>
                <h2 class="text-lg font-bold text-white">
                    File Tidak Didukung Preview
                </h2>

                <p class="text-sm text-gray-400 mt-1">
                    Silakan download file untuk melihat isi.
                </p>
            </div>

            <a href="{{ asset('storage/' . $filePath) }}" download
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-white font-semibold transition">
                <i class="ri-download-line"></i>
                Download File
            </a>
        </div>
    @endif

</div>
