<x-filament-panels::page>
    <x-filament::card>
        <p class="text-2xl">無償一次通貨の回収</p>
        <br />
        <x-filament-panels::form :wire:key="$this->getId() . '.searchForms.' . $this->getFormStatePath()">
            {{ $this->searchForm }}
            <br />
            <x-filament-panels::form.actions :actions="$this->getSearchFormActoins()" />
        </x-filament-panels::form>
    </x-filament::card>

    @if($searchFormData['userId'])
    <x-filament::card>
        @if($userData)
        <p>
            <span class="font-bold">ユーザーID：{{$userData['userId']}}の所持通貨</span>
            <p class="mt-3">一次通貨：合計 {{number_format($userData['totalAmount'])}}個</p>
            <p class="mt-3">有償一次通貨：合計 {{number_format($userData['paidAmount'])}}個</p>
            <div style="margin-left: 1rem;">
                <p class="mt-3">AppStore一次通貨：合計 {{number_format($userData['paidAmountApple'])}}個</p>
                <p class="mt-3">GooglePlay一次通貨：合計 {{number_format($userData['paidAmountGoogle'])}}個</p>
            </div>

            <p class="mt-3">無償一次通貨：合計 {{number_format($userData['freeAmount'])}}個</p>
            <div style="margin-left: 1rem;">
                <p class="mt-3">ゲーム内配布一次通貨：合計 {{number_format($userData['freeIngameAmount'])}}</p>
                <p class="mt-1">購入ボーナス分無償一次通貨：合計 {{number_format($userData['freeBonusAmount'])}}個</p>
                <p class="mt-1">広告リワード無償一次通貨：合計 {{number_format($userData['freeRewardAmount'])}}個</p>
            </div>
        </p>
        <br />

        <x-filament-panels::form :wire:key="$this->getId() . '.collectForm.' . $this->getFormStatePath()">
            {{ $this->collectForm }}
            <br />

            <x-filament-panels::form.actions :actions="$this->getCollectFormActions()" />
        </x-filament-panels::form>
        @else
        対象ユーザーが見つかりませんでした。
        @endif
    </x-filament::card>
    @endif
</x-filament-panels::page>
