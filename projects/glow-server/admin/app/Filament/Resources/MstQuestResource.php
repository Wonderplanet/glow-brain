<?php

namespace App\Filament\Resources;

use App\Constants\ImagePath;
use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Constants\QuestType;
use App\Constants\RarityType;
use App\Filament\Authorizable;
use App\Filament\Pages\QuestDetail;
use App\Filament\Resources\MstQuestResource\Pages;
use App\Models\Mst\MstQuest;
use App\Tables\Columns\AssetImageColumn;
use App\Utils\TimeUtil;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MstQuestResource extends Resource
{

    protected static ?string $model = MstQuest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $modelLabel = 'クエスト';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::QUEST_DISPLAY_ORDER->value; // メニューの並び順

    public static function table(Table $table): Table
    {
        $query = MstQuest::query()
            ->with(
                'mst_quest_i18n',
                'mst_event',
                'mst_event.mst_event_i18n',
                'mst_stages',
                'mst_stages.mst_stage_event_setting',
            );

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('id')
                    ->label('クエストID')
                    ->sortable(),
                TextColumn::make('quest_type')
                    ->label('クエストタイプ')
                    ->sortable(),
                TextColumn::make('mst_quest_i18n.name')
                    ->label('クエスト名')
                    ->sortable(),
                AssetImageColumn::make('asset_image')->label('バナー'),
                TextColumn::make('sort_order')
                    ->label('並び順')
                    ->sortable(),
                TextColumn::make('event')
                    ->label('イベント')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->mst_event ? '[' . $record->mst_event->id . ']' . ($record->mst_event->mst_event_i18n?->name ?? '') : '';
                    }),
                TextColumn::make('start_date')
                    ->label('開始日')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('終了日')
                    ->sortable(),
            ])
            ->searchable(false)
            ->deferFilters()
            ->hiddenFilterIndicators()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )
            ->filters([
                Filter::make('id')
                    ->form([
                        TextInput::make('id')
                            ->label('クエストID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id'])) {
                            return $query;
                        }
                        return $query->where('id', "{$data['id']}");
                    }),
                Filter::make('name')
                    ->form([
                        TextInput::make('name')
                            ->label('クエスト名')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['name'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_quest_i18n', function ($query) use ($data) {
                            $query->where('name', 'like', "%{$data['name']}%");
                        });
                    }),
                SelectFilter::make('quest_type')
                    ->options(QuestType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('quest_type', $data);
                    })
                    ->label('クエストタイプ'),
                Filter::make('event_id')
                    ->form([
                        TextInput::make('event_id')
                            ->label('イベントID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['event_id'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_event', function ($query) use ($data) {
                            $query->where('id', 'like', "%{$data['event_id']}%");
                        });
                    }),
                Filter::make('event_name')
                    ->form([
                        TextInput::make('event_name')
                            ->label('イベント名')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['event_name'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_event', function ($query) use ($data) {
                            $query->whereHas('mst_event_i18n', function ($query) use ($data) {
                                $query->where('name', 'like', "%{$data['event_name']}%");
                            });
                        });
                    }),
                Filter::make('start_date')
                    ->form([
                        DateTimePicker::make('start_date')
                            ->label('開始日')
                            ->withoutTime()
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return TimeUtil::addWhereBetweenByDay($query, 'start_date', $data);
                    }),
                Filter::make('end_date')
                    ->form([
                        DateTimePicker::make('end_date')
                            ->label('終了日')
                            ->withoutTime()
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return TimeUtil::addWhereBetweenByDay($query, 'end_date', $data);
                    }),
                Filter::make('has_stage_rule')
                    ->form([
                        Checkbox::make('has_stage_rule')
                            ->label('リミテッドバトルを含む')
                    ])
                    ->query(function (Builder $query, $data): Builder {
                        if ($data['has_stage_rule'] ?? false) {
                            return $query->whereHas('mst_stages', function ($query) use ($data) {
                                $query->whereHas('mst_stage_event_setting', function ($query) use ($data) {
                                    $query->whereNotNull('mst_stage_rule_group_id');
                                });
                            });
                        }
                        return $query;
                    }),
                Filter::make('has_stage_rule_speed_attack')
                    ->form([
                        Checkbox::make('has_stage_rule_speed_attack')
                            ->label('スピードアタックを含む')
                    ])
                    ->query(function (Builder $query, $data): Builder {
                        if ($data['has_stage_rule_speed_attack'] ?? false) {
                            return $query->whereHas('mst_stages', function ($query) use ($data) {
                                $query->whereHas('mst_stage_event_setting', function ($query) use ($data) {
                                    $query->where('stage_event_type', $data['has_stage_rule_speed_attack']);
                                });
                            });
                        }
                        return $query;
                    })
            ], FiltersLayout::AboveContent)
            ->actions([
                Action::make('detail')
                    ->label('詳細')
                    ->button()
                    ->url(function (Model $record) {
                        return QuestDetail::getUrl([
                            'questId' => $record->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns)
            ->emptyStateActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMstQuests::route('/'),
        ];
    }
}
