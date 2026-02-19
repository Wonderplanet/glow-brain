@php
    use App\Constants\UserStatus;
@endphp
<x-filament-panels::page>
    <div>
        <x-filament::breadcrumbs :breadcrumbs="$this->breadcrumbList" />
    </div>
    @if ($this->status === UserStatus::BAN_PERMANENT->value)
        <p style="color: red; font-size:2rem;">
            ※アカウント永久停止解除は、アカウント停止状態の解除ではなく、<br>
            アカウント一時停止への切り替えになりますのでご注意ください。
        </p>
    @endif
    <form wire:submit.prevent="update">
        {{$this->form}}
    </form>
</x-filament-panels::page>
