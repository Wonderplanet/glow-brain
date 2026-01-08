<x-filament-panels::page>
    {{ $this->infoList }}
    <x-rewards-table :title="'ランキング順位に準じた報酬情報'" :rows="$this->pvpRewardTable('Ranking')" />
    <x-rewards-table :title="'ランクに準じた報酬情報'" :rows="$this->pvpRewardTable('RankClass')" />
</x-filament-panels::page>
