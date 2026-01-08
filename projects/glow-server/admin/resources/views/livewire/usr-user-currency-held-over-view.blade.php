<x-filament-widgets::widget>
    <x-filament::section>
        <p class="text-2xl">
            ユーザーID：<span class="font-bold">{{$userId}}</span>
            @if($existsUsrUser)
                <span style="margin-left: 5rem;"></span>
                ユーザー名：<span class="font-bold">{{$userName}}</span>
            @else
                <br/>
                <span class="font-bold text-danger-600">該当ユーザーが存在しません</span>
            @endif
        </p>
        <br/>
        @if($existsUsrCurrencyFree || $existsUsrCurrencySummary)
            <p class="mt-3">一次通貨：全合計 {{number_format($primaryCurrencyAmount)}}個</p>
        @endif
        @if($existsUsrCurrencySummary)
            <p class="mt-3">有償一次通貨：合計 {{number_format($totalPaidAmount)}}個</p>
            <div style="margin-left: 1rem;">
                <p class="mt-3">AppStore一次通貨：合計 {{number_format($paidAmountApple)}}個</p>
                <p class="mt-3">GooglePlay一次通貨：合計 {{number_format($paidAmountGoogle)}}個</p>
            </div>
        @else
            <p class="mt-3 font-bold text-danger-600">通貨サマリーが存在しません</p>
        @endif
        @if($existsUsrCurrencyFree)
            <p class="mt-3">無償一次通貨：合計 {{number_format($totalFreeAmount)}}個</p>
            <div style="margin-left: 1rem;">
                <p class="mt-3">ゲーム内配布一次通貨：合計 {{number_format($ingameAmount)}}</p>
                <p class="mt-1">購入ボーナス分無償一次通貨：合計 {{number_format($bonusAmount)}}個</p>
                <p class="mt-1">広告リワード無償一次通貨：合計 {{number_format($rewardAmount)}}個</p>
            </div>
        @else
            <p class="mt-3 font-bold text-danger-600">無償一次通貨データが存在しません</p>
        @endif
        @if($existsUsrCurrencySummary)
            <p class="mt-6">二次通貨：合計 {{number_format($summariesCash)}}個</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
