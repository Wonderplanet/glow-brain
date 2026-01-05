<div>
    @if($userId)
    <x-filament::section collapsible>
        <x-slot name="heading">
            ユーザー課金情報
        </x-slot>

        {{ $this->infoList }}
    </x-filament::section>
    @endif
</div>
