@props(['type' => 'button', 'full' => false])

<button type="{{ $type }}"
    class="{{ $full ? 'w-full' : '' }} inline-flex items-center justify-center px-4 py-3 text-sm font-semibold text-white bg-blue-600 border border-transparent rounded-lg gap-x-2 hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
    {{ $slot }}
</button>
