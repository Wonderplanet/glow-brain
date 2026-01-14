<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Filament\Resources\OprProductResource;
use App\Models\Opr\OprProduct;
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

class OprProductDetail extends MstDetailBasePage implements HasTable
{
    use InteractsWithTable;
    use InteractsWithInfolists;
    use RewardInfoGetTrait;

    protected static string $view = 'filament.pages.opr-product-detail';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'ショップ課金商品詳細';

    public string $productSubId = '';

    protected $queryString = [
        'productSubId',
    ];

    protected function getResourceClass(): ?string
    {
        return OprProductResource::class;
    }

    protected function getMstModelByQuery(): ?OprProduct
    {
        return OprProduct::query()
            ->where('id', $this->productSubId)
            ->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('opr_products.id: %s', $this->productSubId);
    }

    protected function getSubTitle(): string
    {
        return StringUtil::makeIdNameViewString(
            $this->productSubId,
            '',
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(OprProduct::query())
            ->searchable(false)
            ->columns([
                TextColumn::make('id')
                    ->label('Id')
                    ->searchable()
                    ->sortable(),
            ]);
    }

    private function infoList(): Infolist
    {
        $oprProduct = $this->getMstModel();

        $state = [
            'id' => $oprProduct->id,
            'product_id_ios' => $oprProduct?->mst_store_product->product_id_ios ?? '-',
            'product_id_aos' => $oprProduct?->mst_store_product->product_id_android ?? '-',
            'price_ios' => $oprProduct?->mst_store_product?->mst_store_product_i18n->price_ios ?? '-',
            'price_aos' => $oprProduct?->mst_store_product?->mst_store_product_i18n->price_android ?? '-',
            'name' => $oprProduct?->mst_pack->mst_pack_i18n->name ?? '-',
            'product_type' => $oprProduct->productTypeLabel,
            'purchasable_count' => $oprProduct->purchasable_count ?? '無制限',
            'paid_amount' => $oprProduct->paid_amount,
            'display_priority' => $oprProduct->display_priority,
            'term' => $oprProduct->start_date . ' ~ ' . $oprProduct->end_date,
            'release_key' => $oprProduct->release_key,
        ];
        $fieldset = Fieldset::make('ショップ課金商品詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('name')->label('パック名'),
                TextEntry::make('term')->label('有効期間'),
                TextEntry::make('product_id_ios')->label('AppStoreの商品ID'),
                TextEntry::make('product_id_aos')->label('Google Play Storeの商品ID'),
                TextEntry::make('price_ios')->label('AppStoreの金額'),
                TextEntry::make('price_aos')->label('Google Play Storeの金額'),
                TextEntry::make('product_type')->label('商品種別'),
                TextEntry::make('purchasable_count')->label('購入可能回数'),
                TextEntry::make('paid_amount')->label('有償プリズム付与数'),
                TextEntry::make('display_priority')->label('表示優先度'),
                TextEntry::make('release_key')->label('リリースキー'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function getPackContents(): array
    {
        $oprProduct = $this->getMstModel();

        $mstPackContents = $oprProduct->mst_pack?->mst_pack_contents ?? collect();
        if ($mstPackContents->isEmpty()) {
            return [];
        }

        $rewardInfos = $this->getRewardInfos($mstPackContents->map->reward);

        $rows = [];
        foreach ($mstPackContents as $mstPackContent) {
            $rewardInfo = $rewardInfos->get($mstPackContent->id);
            if (is_null($rewardInfo)) {
                continue;
            }
            $rows[] = [
                'id' => $mstPackContent->id,
                'rewardInfo' => $rewardInfo,
                'isBonus' => $mstPackContent->is_bonus,
                'displayOrder' => $mstPackContent->display_order,
            ];
        }

        return $rows;
    }
}
