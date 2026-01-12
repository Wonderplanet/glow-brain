<x-filament-panels::page>
    <x-description-list :title="'基本情報'" :list="$this->getBasicInfo()" />
    <x-description-list :title="'表示条件'" :list="$this->getDisplayInfo()" />
    <x-description-list :title="'アセット情報'" :list="$this->getAssetInfo()" />
    <x-rewards-table :title="'消費コスト'" :rows="$this->getUseResourceTableRows()" />

    <x-filament::section heading="ガシャを引ける上限設定">
        <x-description-list :title="'通常'" :list="$this->getDrawByNormalInfos()" />
        <x-description-list :title="'広告で引く'" :list="$this->getDrawByAdInfos()" />
    </x-filament::card>

    <x-table :title="'天井'" :rows="$this->getUpperTableRows()" />
    <x-description-list :title="'訴求文言'" :list="$this->getAdditionalDescriptions()" />
    <x-rewards-table :title="'訴求キャラ'" :rows="$this->getDisplayUnitTableRows()" />
    <x-rewards-table :title="'確率'" :rows="$this->getGachaProbabilityInfos()" />
    <x-description-list :title="'N連'" :list="$this->getMultiDrawInfos()" />
    <x-rewards-table :title="'通常枠テーブル'" :rows="$this->getPrizeTableRows()" />
    <x-rewards-table :title="'確定枠テーブル'" :rows="$this->getFixedPrizeTableRows()" />
    <x-rewards-table :title="'最高レアリティ天井枠テーブル'" :rows="$this->getUpperMaxRarityPrizeTableRows()" />
    <x-rewards-table :title="'ピックアップ天井枠テーブル'" :rows="$this->getUpperPickupPrizeTableRows()" />
    <x-table :title="'キャラのかけら変換個数'" :rows="$this->getUnitFragmentConvertTableRows()" />
</x-filament-panels::page>
