<x-filament-panels::page>
    {{ $this->infoList }}
    {{ $this->adventBattleRankTable() }}
    <x-table :title="'編成ルール'" :rows="$this->inGameSpecialRuleTable()" />
    <x-rewards-table :title="'最大スコアに応じた報酬情報'" :rows="$this->adventBattleRewardTable('MaxScore')" />
    <x-rewards-table :title="'ランキング順位に準じた報酬情報'" :rows="$this->adventBattleRewardTable('Ranking')" />
    <x-rewards-table :title="'ランクに準じた報酬情報'" :rows="$this->adventBattleRewardTable('Rank')" />
    <x-rewards-table :title="'協力バトルのダメージ報酬情報'" :rows="$this->adventBattleRewardTable('RaidTotalScore')" />
</x-filament-panels::page>
