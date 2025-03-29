<div class="space-y-2">
    <div class="flex justify-between">
        <span class="font-medium">${{ number_format($amount, 2) }} de ${{ number_format($total, 2) }}</span>
        <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 dark:bg-{{ $statusColor }}-900 dark:text-{{ $statusColor }}-300">
            {{ $statusText }}
        </span>
    </div>

    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
        <div class="bg-{{ $statusColor }}-500 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
    </div>
</div>
