<x-filament-panels::page>
    <x-filament::card>
        <p class="text-2xl">対象環境名:{{config('app.env')}}</p>
    </x-filament::card>

    <x-filament::card>
        <p class="text-2xl">通貨デバッグ付与</p>
        <br />
        <form wire:submit='addCurrency'>
            {{ $this->form }}
            <br />
            @if(!empty($userData))
                <p>
                    <span class="font-bold">ユーザーID：{{$userData['userId']}}の所持通貨</span>
                    <span style="margin-left: 1rem;">総量：{{$userData['totalAmount']}}個</span>
                    <span style="margin-left: 1rem;">有償：{{$userData['paidAmount']}}個</span>
                    <span style="margin-left: 1rem;">無償：{{$userData['freeAmount']}}個</span>
                </p>
            @endif
            {{ $this->addButton }}
        </form>
    </x-filament::card>
</x-filament-panels::page>
