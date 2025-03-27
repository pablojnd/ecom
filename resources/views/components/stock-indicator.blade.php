<div>
    <span class="{{ $colorClass }}">
        {{ $stock }}
        @if ($stock <= 0)
            <span class="text-danger-500 font-medium"> (Sin stock)</span>
        @elseif ($stock <= 5)
            <span class="text-warning-500 font-medium"> (Bajo)</span>
        @endif
    </span>
</div>
