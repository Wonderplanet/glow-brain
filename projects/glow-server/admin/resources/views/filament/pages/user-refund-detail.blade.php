@php
    use App\Constants\UserStatus;
@endphp
<x-filament-panels::page>
    <div>
        <x-filament::breadcrumbs :breadcrumbs="$this->breadcrumbList" />
    </div>
    {{$this->userInfolist}}
    {{$this->table}}
</x-filament-panels::page>
