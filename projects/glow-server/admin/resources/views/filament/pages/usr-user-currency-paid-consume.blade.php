<x-filament-panels::page>
    <x-filament::card>
        <p class="text-2xl">対象環境名:{{config('app.env')}}</p>
    </x-filament::card>

    <x-filament::card>
        <x-filament-panels::form
            :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()">
            <p class="text-2xl">一次通貨デバッグ消費</p>
            <p>下記の点にご注意ください</p>
            <p class="text-sm">
                <span>・無償 + 有償から消費する場合、無償 > 有償の優先度で消費されます</span>
                <br />
                <span>・無償通貨で不足した分は、指定したプラットフォームの通貨で消費されます</span>
                <br />
                <span>・指定したプラットフォームの通貨が足りない場合はエラーとなります</span>
                <br />
                <span>・無償通貨のみで消費することはできません</span>
                <br />
                <span>・二次通貨の消費はされません</span>
            </p>
            {{ $this->form }}
            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
            @if(!empty($userData))
                <p>
                    <span class="font-bold">ユーザーID：{{$userData['userId']}}の所持通貨</span>
                    <br />
                    <span style="margin-left: 1rem;">総量：{{$userData['totalAmount']}}個</span>
                    <br />
                    <span style="margin-left: 1rem;">有償：{{$userData['paidAmount']}}個</span>
                    <br />
                    <span style="margin-left: 2rem;">AppleStore購入分：{{$userData['paidAmountApple']}}個</span>
                    <br />
                    <span style="margin-left: 2rem;">GooglePlay購入分：{{$userData['paidAmountGoogle']}}個</span>
                    <br />
                    <span style="margin-left: 1rem;">無償：{{$userData['freeAmount']}}個</span>
                </p>
            @endif
        </x-filament-panels::form>
    </x-filament::card>
</x-filament-panels::page>
