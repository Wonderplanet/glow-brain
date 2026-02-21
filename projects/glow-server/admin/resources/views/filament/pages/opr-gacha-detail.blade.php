<x-filament-panels::page>
    {{-- 基本情報 | アセット情報 --}}
    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
        <x-description-list :title="'基本情報'" :list="$this->getBasicInfo()" :compact="true" :color="'blue'" />
        <x-description-list :title="'アセット情報'" :list="$this->getAssetInfo()" :compact="true" :color="'blue'" />
    </div>

    {{-- 訴求 --}}
    <x-filament::card>
        <div style="margin: -1.5rem -1.5rem 1rem; padding: 0.6rem 1.5rem; background-color: #eff6ff; border-bottom: 1px solid #bfdbfe; border-radius: 0.375rem 0.375rem 0 0;">
            <h3 style="font-weight: 700; color: #1e40af; font-size: 0.875rem;">訴求</h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
            <x-description-list :title="'訴求文言'" :list="$this->getAdditionalDescriptions()" :compact="true" :color="'blue'" />
            <x-rewards-table :title="'訴求キャラ'" :rows="$this->getDisplayUnitTableRows()" :color="'blue'" />
        </div>
    </x-filament::card>

    {{-- ガシャを引ける上限設定 --}}
    <x-filament::card>
        <div style="margin: -1.5rem -1.5rem 1rem; padding: 0.6rem 1.5rem; background-color: #eff6ff; border-bottom: 1px solid #bfdbfe; border-radius: 0.375rem 0.375rem 0 0;">
            <h3 style="font-weight: 700; color: #1e40af; font-size: 0.875rem;">ガシャを引ける上限設定</h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
            <x-description-list :title="'通常'" :list="$this->getDrawByNormalInfos()" :compact="true" :color="'blue'" />
            <x-description-list :title="'広告で引く'" :list="$this->getDrawByAdInfos()" :compact="true" :color="'blue'" />
        </div>
    </x-filament::card>

    <x-rewards-table :title="'消費コスト'" :rows="$this->getUseResourceTableRows()" :color="'blue'" />

    @if(!$this->isStepupGacha())
        <x-rewards-table :title="'レアリティ別 確率'" :rows="$this->getGachaProbabilityInfos()" :color="'blue'" />
        {{-- 通常枠テーブル | 確定枠テーブル --}}
        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
            <x-rewards-table :title="'通常枠テーブル'" :rows="$this->getPrizeTableRows()" :color="'orange'" />
            <x-rewards-table :title="'確定枠テーブル'" :rows="$this->getFixedPrizeTableRows()" :color="'orange'" />
        </div>
        {{-- 天井 --}}
        <x-table :title="'天井設定'" :rows="$this->getUpperTableRows()" :color="'purple'" />
        {{-- 最高レアリティ天井枠テーブル | ピックアップ天井枠テーブル --}}
        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
            <x-rewards-table :title="'最高レアリティ天井枠テーブル'" :rows="$this->getUpperMaxRarityPrizeTableRows()" :color="'purple'" />
            <x-rewards-table :title="'ピックアップ天井枠テーブル'" :rows="$this->getUpperPickupPrizeTableRows()" :color="'purple'" />
        </div>
    @else
        {{-- ステップアップ設定 | ステップ設定テーブル --}}
        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
            <x-description-list :title="'ステップアップ基本設定'" :list="$this->getStepupGachaBasicInfo()" :compact="true" :color="'blue'" />
            <x-rewards-table :title="'ステップ設定'" :rows="$this->getStepupGachaStepTableRows()" :color="'blue'" />
        </div>
        @php $stepRewardGroups = $this->getStepupGachaStepRewardTableRows(); @endphp
        @if(!empty($stepRewardGroups))
            <x-filament::card>
                <div style="margin: -1.5rem -1.5rem 1rem; padding: 0.6rem 1.5rem; background-color: #f0fdf4; border-bottom: 1px solid #bbf7d0; border-radius: 0.375rem 0.375rem 0 0;">
                    <h3 style="font-weight: 700; color: #15803d; font-size: 0.875rem;">ステップおまけ報酬</h3>
                </div>
                <div class="flex flex-col gap-4">
                    @foreach($stepRewardGroups as $stepNumber => $loopGroups)
                        <div class="grid gap-4" style="grid-template-columns: repeat({{ count($loopGroups) }}, minmax(0, 1fr));">
                            @foreach($loopGroups as $group)
                                <x-rewards-table :title="'ステップ' . $stepNumber . ' / ' . $group['loop_count_label']" :rows="$group['rows']" :color="'green'" />
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </x-filament::card>
        @endif
        {{-- ステップごとに通常枠 | 確定枠 をセクションに内包 --}}
        @foreach($this->getStepupGachaStepPrizeGroups() as $stepNumber => $group)
            <x-filament::card>
                <div style="margin: -1.5rem -1.5rem 1rem; padding: 0.6rem 1.5rem; background-color: #f9fafb; border-bottom: 1px solid #e5e7eb; border-radius: 0.375rem 0.375rem 0 0;">
                    <h3 style="font-weight: 700; color: #374151; font-size: 0.875rem;">ステップ {{ $stepNumber }} 抽選テーブル</h3>
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
                    <x-rewards-table :title="'ステップ' . $stepNumber . ' 通常枠（ID: ' . ($group['prize']['prize_group_id'] ?? 'なし') . '）'" :rows="$group['prize']['rows']" :color="'orange'" />
                    @if($group['fixed_prize'] !== null)
                        <x-rewards-table
                            :title="'ステップ' . $stepNumber . ' 確定枠（確定枠数: ' . $group['fixed_prize']['fixed_prize_count'] . ' / 閾値: ' . $group['fixed_prize']['threshold'] . ' / ID: ' . $group['fixed_prize']['fixed_prize_group_id'] . '）'"
                            :rows="$group['fixed_prize']['rows']"
                            :color="'orange'"
                        />
                    @else
                        <x-rewards-table :title="'ステップ' . $stepNumber . ' 確定枠'" :rows="[]" :color="'orange'" />
                    @endif
                </div>
            </x-filament::card>
        @endforeach
    @endif

    <x-table :title="'キャラのかけら変換個数'" :rows="$this->getUnitFragmentConvertTableRows()" :color="'blue'" />
</x-filament-panels::page>
