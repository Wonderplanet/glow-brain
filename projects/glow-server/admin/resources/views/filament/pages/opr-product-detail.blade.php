@php
    $contents = $this->getPackContents();
@endphp
<x-filament-panels::page>
    <!-- 基本情報 -->
    {{ $this->infoList }}

    @if (!empty($contents))
        <!-- パック情報 -->
        <x-reward-info-table title="パック内包物">
            <x-reward-info-table-column
                    :columns="[
                    ['prop' => 'id', 'label' => 'ID'],
                    ['prop' => 'rewardInfo', 'label' => '内包物情報', 'component' => 'reward-info'],
                    ['prop' => 'isBonus', 'label' => 'おまけフラグ'],
                    ['prop' => 'displayOrder', 'label' => '表示順序'],
                ]"
                    :rows="$contents"
            />
        </x-reward-info-table>
    @endif
</x-filament-panels::page>
