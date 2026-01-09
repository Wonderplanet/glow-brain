<!-- Filamentにより生成されたボタンのHTMLを参考にしている -->
@props([
    'disabled' => false,
])

@php
    $color = $disabled ? 'gray' : 'primary';
    $style = "--c-400:var(--{$color}-400);--c-500:var(--{$color}-500);--c-600:var(--{$color}-600);";
@endphp

<button type="submit" {{ ($disabled) ? ' disabled' : '' }} style="{{ $style }}" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 dark:bg-custom-500 dark:hover:bg-custom-400 focus:ring-custom-500/50 dark:focus:ring-custom-400/50 fi-ac-btn-action">
    {{ $slot }}
</button>
