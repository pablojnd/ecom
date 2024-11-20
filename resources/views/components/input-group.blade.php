@props(['label', 'for', 'type' => 'text', 'model'])

<div>
    <label for="{{ $for }}" class="block mb-2 text-sm dark:text-white">{{ $label }}</label>
    <div class="relative">
        <input type="{{ $type }}" id="{{ $for }}" wire:model="{{ $model }}"
            class="block w-full px-4 py-3 text-sm border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600"
            required>
        @error($model)
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="w-5 h-5 text-red-500" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"
                aria-hidden="true">
                <path
                    d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z" />
            </svg>
        </div>
        @enderror
    </div>
    @error($model)
    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
