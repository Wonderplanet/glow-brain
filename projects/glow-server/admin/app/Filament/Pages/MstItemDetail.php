<?php

namespace App\Filament\Pages;

use App\Constants\ImagePath;
use App\Domain\Item\Enums\ItemType;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Filament\Resources\MstItemResource;
use App\Infolists\Components\AssetImageEntry;
use App\Models\Mst\MstFragmentBox;
use App\Models\Mst\MstFragmentBoxGroup;
use App\Models\Mst\MstItem;
use App\Models\Mst\MstUnit;
use App\Tables\Columns\RewardInfoColumn;
use App\Traits\RewardInfoGetTrait;
use App\Utils\AssetUtil;
use App\Utils\StringUtil;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MstItemDetail extends MstDetailBasePage implements HasTable
{
    use InteractsWithTable;
    use InteractsWithInfolists;
    use RewardInfoGetTrait;

    protected static string $view = 'filament.pages.mst-item-detail';

    protected static ?string $title = 'アイテム詳細';

    public string $mstItemId = '';

    protected $queryString = [
        'mstItemId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstItemResource::class;
    }

    protected function getMstModelByQuery(): ?MstItem
    {
        return MstItem::query()
            ->where('id', $this->mstItemId)
            ->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('アイテムID: %s', $this->mstItemId);
    }

    protected function getSubTitle(): string
    {
        return StringUtil::makeIdNameViewString(
            $this->mstItemId,
            $this->getMstModel()->getName(),
        );
    }

    public function table(Table $table): Table
    {
        $query = MstItem::query()
            ->with('mst_item_i18n');

        return $table
            ->query($query)
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
        $mstItem = $this->getMstModel();
        $mstItemI18n = $mstItem->mst_item_i18n;

        $state = [
            'id' => $mstItem->id,
            'type' => $mstItem->getItemTypeLabelAttribute(),
            'group_type' => $mstItem->group_type,
            'rarity' => $mstItem->rarity,
            'asset_key' => $mstItem->asset_key,
            'effect_value' => $mstItem->effect_value,
            'sort_order' => $mstItem->sort_order,
            'term' => $mstItem->start_date . ' ~ ' . $mstItem->end_date,
            'release_key' => $mstItem->release_key,
            'name' => $mstItemI18n?->name ?? '',
            'description' => $mstItemI18n?->description ?? '',
            'asset_image' => $mstItem,
        ];
        $fieldset = Fieldset::make('アイテム詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('name')->label('アイテム名'),
                TextEntry::make('description')->label('説明'),
                TextEntry::make('term')->label('有効期間'),
                TextEntry::make('type')->label('アイテム種別'),
                TextEntry::make('group_type')->label('グループ種別'),
                TextEntry::make('rarity')->label('レアリティ'),
                TextEntry::make('asset_key')->label('アセットキー'),
                TextEntry::make('effect_value')->label('効果値'),
                TextEntry::make('sort_order')->label('表示順'),
                TextEntry::make('release_key')->label('リリースキー'),
                AssetImageEntry::make('asset_image')->label('アイテム画像'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function characterFragmentInfoList(): ?Infolist
    {
        $mstItem = $this->getMstModel();

        if ($mstItem->isCharacterFragment() === false) {
            return null;
        }

        $mstUnit = MstUnit::query()
            ->with('mst_unit_i18n')
            ->where('fragment_mst_item_id', $mstItem->id)->first();

        if ($mstUnit === null) {
            return null;
        }
        $mstUnitI18n = $mstUnit?->mst_unit_i18n;

        $state = [
            'mst_unit_id' => $mstUnit->id,
            'mst_unit_name' => $mstUnitI18n?->name ?? '',
        ];

        $fieldset = Fieldset::make(ItemType::CHARACTER_FRAGMENT->label() . ' ' . 'ユニット情報')
            ->schema([
                TextEntry::make('mst_unit_id')->label('ユニットID'),
                TextEntry::make('mst_unit_name')->label('ユニット名'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function randomFragmentBoxTable(): ?Table
    {
        $mstItem = $this->getMstModel();

        if ($mstItem->isRandomFragmentBox() === false) {
            return null;
        }

        $mstFragmentBox = MstFragmentBox::query()
            ->with('mst_fragment_box_groups')
            ->where('mst_item_id', $mstItem->id)
            ->first();
        if ($mstFragmentBox === null) {
            return null;
        }

        $query = MstFragmentBoxGroup::query()
            ->where('mst_fragment_box_group_id', $mstFragmentBox->mst_fragment_box_group_id);

        $rewardInfos = $this->getRewardInfos($query->get()->map->reward);

        return $this->getTable()
            ->heading('ランダムかけらBOX')
            ->query($query)
            ->columns([
                TextColumn::make('id')->label('かけらBOXグループID'),
                RewardInfoColumn::make('reward_info')->label('抽選対象アイテム')
                    ->getStateUsing(
                        function (MstFragmentBoxGroup $model) use ($rewardInfos) {
                            return $rewardInfos->get($model->id);
                        }
                    ),
                TextColumn::make('start_at')->label('開始日時'),
                TextColumn::make('end_at')->label('終了日時'),
            ])
            ->paginated(false);
    }

    public function selectionFragmentBoxTable(): ?Table
    {
        $mstItem = $this->getMstModel();

        if ($mstItem->isSelectionFragmentBox() === false) {
            return null;
        }

        $mstFragmentBox = MstFragmentBox::query()
            ->with('mst_fragment_box_groups')
            ->where('mst_item_id', $mstItem->id)
            ->first();
        if ($mstFragmentBox === null) {
            return null;
        }

        $query = MstFragmentBoxGroup::query()
            ->where('mst_fragment_box_group_id', $mstFragmentBox->mst_fragment_box_group_id);

        $rewardInfos = $this->getRewardInfos($query->get()->map->reward);

        return $this->getTable()
            ->heading('選択かけらBOX')
            ->query($query)
            ->columns([
                TextColumn::make('id')->label('かけらBOXグループID'),
                RewardInfoColumn::make('reward_info')->label('選択対象アイテム')
                    ->getStateUsing(
                        function (MstFragmentBoxGroup $model) use ($rewardInfos) {
                            return $rewardInfos->get($model->id);
                        }
                    ),
                TextColumn::make('start_at')->label('開始日時'),
                TextColumn::make('end_at')->label('終了日時'),
            ])
            ->paginated(false);
    }
}
