@php
    use App\Constants\UserStatus;
@endphp
<x-filament-panels::page>
    <div>
        <x-filament::breadcrumbs :breadcrumbs="$this->breadcrumbList" />
    </div>
    @if ($this->status === UserStatus::DELETED->value)
        <div style="border-radius: 10px; background-color:#F00; font-size:5rem; color:#fff;">
            <p>
                対象のアカウントは、削除されています。
            </p>
        </div>
    @endif
    {{$this->userInfoList}}
    {{$this->userProfilInfoList}}
</x-filament-panels::page>
