<?php

namespace App\Filament\Pages;

use App\Constants\ImagePath;
use App\Constants\UserSearchTabs;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Stage\Enums\TreasureRank;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\MstItem;
use App\Models\Usr\UsrItem;
use App\Tables\Columns\MstIdColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserItem extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-item';

    public string $currentTab = UserSearchTabs::ITEM->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    public function table(Table $table): Table
    {
        $query = MstItem::query()
            ->with([
                'mst_item_i18n',
            ]);

        $usrItems = UsrItem::query()
            ->where('usr_user_id', $this->userId)
            ->get()
            ->keyBy('mst_item_id');

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->getStateUsing(
                        function (MstItem $mstItem) use ($usrItems) {
                            $usrItem = $usrItems->get($mstItem->id);
                            return $usrItem?->id ?? 'レコードなし';
                        }
                    ),
                MstIdColumn::make('mst_item_info')
                    ->label('アイテム情報')
                    ->searchable()
                    ->getMstIdUsing(function (MstItem $model) {
                        return $model->id;
                    })
                    ->getMstDataNameUsing(function (MstItem $model) {
                        return $model->mst_item_i18n->name;
                    })
                    ->getMstDetailPageUrlUsing(function (MstItem $model) {
                        return MstItemDetail::getUrl(['mstItemId' => $model->id]);
                    }),
                TextColumn::make('amount')
                    ->label('所持数')
                    ->searchable()
                    ->getStateUsing(
                        function (MstItem $mstItem) use ($usrItems) {
                            $usrItem = $usrItems->get($mstItem->id);
                            return $usrItem?->amount ?? 0;
                        }
                    ),
                ])
                ->filters([
                    Filter::make('name')
                        ->form([
                            TextInput::make('name')
                                ->label('アイテム名')
                        ])
                        ->label('アイテム名')
                        ->query(function (Builder $query, array $data): Builder {
                            if (blank($data['name'])) {
                                return $query;
                            }
                            return $query->whereHas('mst_item_i18n', function ($query) use ($data) {
                                $query->where('name', 'like', "%{$data['name']}%");
                            });
                        }),
                    SelectFilter::make('item_type')
                        ->options(ItemType::labels()->toArray())
                        ->query(function (Builder $query, $data): Builder {
                            if (blank($data['value'])) {
                                return $query;
                            }
                            return $query->where('item_type', $data);
                        })
                        ->label('アイテムタイプ'),
                    Filter::make('group_type')
                        ->form([
                            TextInput::make('group_type')
                                ->label('グループタイプ')
                        ])
                        ->label('グループタイプ')
                        ->query(function (Builder $query, array $data): Builder {
                            if (blank($data['group_type'])) {
                                return $query;
                            }
                            return $query->where('group_type', 'like', "%{$data['group_type']}%");
                        }),
                    SelectFilter::make('rarity')
                        ->options(TreasureRank::labels()->toArray())
                        ->query(function (Builder $query, $data): Builder {
                            if (blank($data['value'])) {
                                return $query;
                            }
                            return $query->where('rarity', $data);
                        })
                        ->label('レアリティ'),
                    SelectFilter::make('duration')
                        ->form([
                            DatePicker::make('datetime')
                                    ->label('有効日時'),
                        ])
                        ->label('有効日時')
                        ->query(function (Builder $query, array $data): Builder {
                            if (blank($data['datetime'])) {
                                return $query;
                            }
                            return $query->where('start_date', '<=', $data['datetime'])
                                ->where('end_date', '>=', $data['datetime']);
                        }),
                    ], FiltersLayout::AboveContent)
                ->deferFilters()
                ->filtersApplyAction(
                    fn (Action $action) => $action
                        ->label('適用'),
                )
                ->actions([
                    Action::make('edit')
                        ->label('編集')
                        ->button()
                        ->url(function (MstItem $record) {
                            return EditUserItem::getUrl([
                                'userId' => $this->userId,
                                'mstItemId' => $record->id,
                            ]);
                        })
                        ->visible(fn () => EditUserItem::canAccess()),
                ], position: ActionsPosition::BeforeColumns);
    }
}
