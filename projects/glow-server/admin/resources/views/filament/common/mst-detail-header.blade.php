@props([
    'subTitle' => '',
    'breadcrumbList' => [],
])

<div>
    <div>
        <x-filament::breadcrumbs :breadcrumbs="$breadcrumbList" />
    </div>
    <div class="mt-2">
        <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
            {{ $subTitle }}
        </h1>
    </div>
</div>
