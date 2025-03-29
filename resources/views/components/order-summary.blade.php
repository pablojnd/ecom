<div class="space-y-2 mt-2">
    <div class="flex justify-between items-center">
        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total de la orden:</span>
        <span class="text-base font-semibold">${{ number_format($orderTotal, 2) }}</span>
    </div>
    
    <div class="flex justify-between items-center">
        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total pagado:</span>
        <span class="text-base font-semibold text-success-600 dark:text-success-400">${{ number_format($paidAmount, 2) }}</span>
    </div>
    
    <div class="flex justify-between items-center">
        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Balance pendiente:</span>
        <span class="text-base font-semibold {{ $balanceDue > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
            ${{ number_format($balanceDue, 2) }}
        </span>
    </div>
    
    @if($orderTotal > 0)
        <div class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mr-2">Estado de pago:</span>
                @if($paidAmount >= $orderTotal)
                    <span class="inline-flex items-center justify-center rounded-full bg-success-50 px-2.5 py-0.5 text-success-700 dark:bg-success-400/10 dark:text-success-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Pagado (100%)
                    </span>
                @elseif($paidAmount > 0)
                    <span class="inline-flex items-center justify-center rounded-full bg-warning-50 px-2.5 py-0.5 text-warning-700 dark:bg-warning-400/10 dark:text-warning-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        Pago parcial ({{ round(($paidAmount / $orderTotal) * 100) }}%)
                    </span>
                @else
                    <span class="inline-flex items-center justify-center rounded-full bg-danger-50 px-2.5 py-0.5 text-danger-700 dark:bg-danger-400/10 dark:text-danger-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        Pendiente de pago
                    </span>
                @endif
            </div>
        </div>
    @endif
</div>
