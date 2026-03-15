@php
    $firstClearRewardTableRows = $this->getFirstClearRewardTableRows();
    $alwaysRewardTableRows = $this->getAlwaysRewardTableRows();
    $inGameSpecialRuleRows = $this->getInGameSpecialRuleRows();
    $enhanceRewardParams = $this->getEnhanceRewardParams();
    $mstClearTimeRewardTableRows = $this->getMstClearTimeRewardTableRows();

@endphp

<x-filament-panels::page>

    {{ $this->infolist }}
    {{ $this->eventSettinglist }}
    <x-reward-table :title="'初回クリア確定報酬'" :rows="$firstClearRewardTableRows" />
    <x-reward-table :title="'クリア報酬'" :rows="$alwaysRewardTableRows" />
    <x-reward-table :title="'イベントルール'" :rows="$inGameSpecialRuleRows" />

    @if ($enhanceRewardParams)
        <x-reward-table :title="'コイン獲得クエストの報酬倍率'" :rows="$enhanceRewardParams" />
    @endif

    @if ($mstClearTimeRewardTableRows)
        <x-reward-table :title="'スピードアタック報酬'" :rows="$mstClearTimeRewardTableRows" />
    @endif

</x-filament-panels::page>
