<?php

namespace App\Filament\Pages;

use App\Constants\PassEffectType;
use App\Constants\ProductType;
use App\Constants\ShopItemResourceType;
use App\Constants\ShopTabs;
use App\Domain\Shop\Enums\PassRewardType;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Infolists\Components\AssetImageEntry;
use App\Models\Mst\MstShopPass;
use App\Models\Mst\MstShopPassEffect;
use App\Models\Mst\MstShopPassReward;
use App\Models\Opr\OprProduct;
use App\Tables\Columns\RewardInfoColumn;
use App\Traits\RewardInfoGetTrait;
use App\Utils\StringUtil;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MstShopPassDetail extends MstDetailBasePage implements HasTable
{
    use RewardInfoGetTrait;
    use InteractsWithTable;
    use InteractsWithInfolists;

    const FIRST_DAY_COUNT = 1;

    protected static string $view = 'filament.pages.mst-shop-pass-detail';
    protected static ?string $title = 'ショップパス詳細';

    public string $mstShopPassId = '';

    protected $queryString = [
        'mstShopPassId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstShopPasses::class;
    }

    protected function getAdditionalBreadcrumbs(): array
    {
        $mstShopPass = $this->getMstModel();
        if ($mstShopPass === null) {
            return [];
        }

        return [
            MstShopPasses::getUrl() => ShopTabs::SHOP_PASS,
        ];
    }

    protected function getMstModelByQuery(): ?MstShopPass
    {
        return MstShopPass::query()
            ->where('id', $this->mstShopPassId)
            ->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('mst_shop_pass.id: %s', $this->mstShopPassId);
    }

    protected function getSubTitle(): string
    {
        $mstShopPass = $this->getMstModel();

        return StringUtil::makeIdNameViewString(
            $mstShopPass->id,
            $mstShopPass->mst_shop_pass_i18n->name,
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MstShopPass::query())
            ->searchable(false)
            ->columns([
                TextColumn::make('id')
                    ->label('Id')
                    ->searchable()
                    ->sortable(),
            ]);
    }

    private function infoList(): InfoList
    {
        $mstShopPass = $this->getMstModel();

        $state = [
            'id' => $mstShopPass->id,
            'is_display_expiration' => $mstShopPass->is_display_expiration ? '表示あり' : '表示なし',
            'name' => $mstShopPass->mst_shop_pass_i18n->name,
            'pass_duration_days' => $mstShopPass->pass_duration_days . '日',
            'release_key' => $mstShopPass->release_key,
            'asset_key' => $mstShopPass->asset_key,
            'asset_image' => $mstShopPass,
            'asset_label' => 'アセットキー: ' . $mstShopPass->asset_key
        ];
        $fieldset = Fieldset::make('パス詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('is_display_expiration')->label('販売有効期限表示有無'),
                TextEntry::make('name')->label('パス名'),
                TextEntry::make('release_key')->label('リリースキー'),
                TextEntry::make('pass_duration_days')->label('パスの有効日数'),
                AssetImageEntry::make('asset_image')
                    ->label('アセット画像'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    private function productInfoList(): InfoList
    {
        $mstShopPass = $this->getMstModel();

        $oprProduct = $mstShopPass->opr_product;

        $productType = '';
        if ($oprProduct->product_type) {
            $productTypeTryFrom = ProductType::tryFrom($oprProduct->product_type);
            $productType = $productTypeTryFrom->label();
        }
        
        $state = [
            'product_type' => $productType,
            'start_date' => $oprProduct->start_date,
            'purchasable_count' => $oprProduct->purchasable_count . '回',
            'end_date' => $oprProduct->end_date,
            'display_priority' => $oprProduct->display_priority,
        ];
        $fieldset = Fieldset::make('商品情報')
            ->schema([
                TextEntry::make('product_type')->label('商品タイプ'),
                TextEntry::make('start_date')->label('開始日'),
                TextEntry::make('purchasable_count')->label('購入回数'),
                TextEntry::make('end_date')->label('終了日'),
                TextEntry::make('display_priority')->label('表示優先度'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function getEffectRows(): array
    {
        $mstShopPassEffectData = MstShopPassEffect::query()
            ->where('mst_shop_pass_id', $this->mstShopPassId)
            ->get();
        $effectTypeData = [];
        foreach ($mstShopPassEffectData as $effect) {
            $effectType = PassEffectType::tryFrom($effect->effect_type);
            if ($effectType) {
                $effectTypeData[] = [
                    'パス効果名' => $effectType->label(),
                    '効果値' => $effectType->detail($effect->effect_value),
                ];
            }
        }

        return $effectTypeData;
    }

    public function getAmountRows(): array
    {
        $mstShopPass = $this->getMstModel();
        $mstShopPassRewards = $mstShopPass->mst_shop_pass_rewards
            ->where('resource_type', ShopItemResourceType::FREE_DIAMOND->value);

        $oprProduct = $mstShopPass->opr_product;

        $paidAmount = $oprProduct->paid_amount ?? 0;
        
        $immediatelyFreeAmountSum = 0;
        $dailyFreeAmountSum = 0;
        foreach ($mstShopPassRewards as $mstShopPassReward) {
            $amount = $mstShopPassReward->resource_amount;
            if ($mstShopPassReward->pass_reward_type === PassRewardType::DAILY->value) {
                $dailyFreeAmountSum += $amount;
            } elseif ($mstShopPassReward->pass_reward_type === PassRewardType::IMMEDIATELY->value) {
                $immediatelyFreeAmountSum += $amount;
            }
        }

        $amountData = [];
        $days = max(0, $mstShopPass->pass_duration_days - self::FIRST_DAY_COUNT);
        $dailyFreeAmountAllDays = $dailyFreeAmountSum * $days;
        $amountData[] = [
            '付与区分' => PassRewardType::IMMEDIATELY->label(),
            '有償' => $paidAmount,
            '無償' => $immediatelyFreeAmountSum,
            '合計' => $paidAmount + $immediatelyFreeAmountSum,
        ];
        $amountData[] = [
            '付与区分' => PassRewardType::DAILY->label(),
            '有償' => 0,
            '無償' => $dailyFreeAmountSum . '個 x ' . $days . '日',
            '合計' => $dailyFreeAmountAllDays,
        ];
        $amountData[] = [
            '付与区分' => '全合計',
            '有償' => $paidAmount,
            '無償' => $immediatelyFreeAmountSum + $dailyFreeAmountAllDays,
            '合計' => $paidAmount + $immediatelyFreeAmountSum + $dailyFreeAmountAllDays,
        ];

        return $amountData;
    }

    public function rewardTable(): ?Table
    {
        $query = MstShopPassReward::query()
            ->where('mst_shop_pass_id', $this->mstShopPassId);

        $rewardDtoList = MstShopPassReward::query()->get()->map(function (MstShopPassReward $mstShopPassReward) {
            return $mstShopPassReward->reward;
        });

        $rewardInfos = $this->getRewardInfos($rewardDtoList);

        return $this->getTable()
            ->heading('パス報酬情報')
            ->query($query)
            ->columns([
                TextColumn::make('pass_reward_type_label')->label('配布方法'),
                RewardInfoColumn::make('reward_info')
                    ->label('報酬情報')
                    ->getStateUsing(
                        function ($record) use ($rewardInfos){
                            return $rewardInfos->get($record->id);
                        }
                    ),
            ])
            ->paginated(false);
    }
}
