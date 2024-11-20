@props(['type' => 'success', 'message'])

<div
    class="p-4 mt-2 text-sm border rounded-lg
    {{ $type === 'error' ? 'text-red-800 bg-red-100 border-red-200 dark:bg-red-800/10 dark:border-red-900 dark:text-red-500' : 'text-green-800 bg-green-100 border-green-200 dark:bg-green-800/10 dark:border-green-900 dark:text-green-500' }}">
    {{ $message }}
</div>
