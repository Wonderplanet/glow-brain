<?php

namespace App\Filament\Resources;

use App\Constants\CampaignTargetIdType;
use App\Constants\CampaignTargetType;
use App\Constants\CampaignType;
use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Constants\QuestDifficulty;
use App\Filament\Authorizable;
use App\Filament\Pages\OprCampaignDetail;
use App\Filament\Resources\OprCampaignResource\Pages;
use App\Models\Opr\OprCampaign;
use App\Utils\TimeUtil;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
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

class OprCampaignResource extends Resource
{

    protected static ?string $model = OprCampaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $modelLabel = 'キャンペーン';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::CAMPAIGN_DISPLAY_ORDER->value; // メニューの並び順

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('Id'),
                TextColumn::make('campaign_type')
                    ->label('キャンペーンタイプ')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function ($record) {
                            return CampaignType::tryFrom($record->campaign_type)->label();
                        }
                    ),
                TextColumn::make('target_type')
                    ->label('対象タイプ')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function ($record) {
                            return CampaignTargetType::tryFrom($record->target_type)->label();
                        }
                    ),
                TextColumn::make('difficulty')
                    ->label('難易度')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function ($record) {
                            if (is_null($record->difficulty)) {
                                return '';
                            } else {
                                return QuestDifficulty::tryFrom($record->difficulty)->label();
                            }
                        }
                    ),
                TextColumn::make('target_id_type')
                    ->label('対象IDタイプ')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function ($record) {
                            if (is_null($record->target_id_type)) {
                                return '';
                            } else {
                                return CampaignTargetIdType::tryFrom($record->target_id_type)->label();
                            }
                        }
                    ),
                TextColumn::make('target_id')
                    ->label('対象ID')
                    ->searchable(),
                TextColumn::make('effect_value')
                    ->label('効果値'),
                TextColumn::make('start_at')
                    ->label('開始日時')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('end_at')
                    ->label('終了日時')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('campaign_type')->label('キャンペーンタイプ')
                    ->options(CampaignType::labels()->toArray()),
                SelectFilter::make('target_type')->label('対象タイプ')
                    ->options(CampaignTargetType::labels()->toArray()),
                SelectFilter::make('difficulty')->label('難易度')
                    ->options(QuestDifficulty::labels()->toArray()),
                SelectFilter::make('target_id_type')->label('対象IDタイプ')
                    ->options(CampaignTargetIdType::labels()->toArray()),
                Filter::make('対象ID')
                    ->form([
                        TextInput::make('target_id')->label('対象ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['target_id'])) {
                            return $query;
                        }
                        return $query->where('target_id', 'like', "%{$data['target_id']}%");
                    }),
                Filter::make('start_at')
                    ->form([
                        DateTimePicker::make('start_at')->label('開始日時')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return TimeUtil::addWhereBetweenByDay($query, 'start_at', $data);
                    }),
                Filter::make('end_at')
                    ->form([
                        DateTimePicker::make('end_at')->label('終了日時')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return TimeUtil::addWhereBetweenByDay($query, 'end_at', $data);
                    }),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )
            ->actions([
                Action::make('detail')
                    ->label('詳細')
                    ->button()
                    ->url(function (Model $record) {
                        return OprCampaignDetail::getUrl([
                            'oprCampaignId' => $record->id,
                        ]);
                    })
            ], position: ActionsPosition::BeforeColumns);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOprCampaign::route('/'),
        ];
    }
}
