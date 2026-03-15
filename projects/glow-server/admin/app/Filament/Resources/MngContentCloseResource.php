<?php

namespace App\Filament\Resources;

use App\Constants\ContentMaintenanceType;
use App\Constants\MaintenanceDisplayOrder;
use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Resources\MngContentCloseResource\Pages;
use App\Models\Mng\MngContentClose;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MngContentCloseResource extends Resource
{
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $model = MngContentClose::class;

    protected static ?string $navigationGroup = NavigationGroups::MAINTENANCE->value;
    protected static ?string $modelLabel = 'コンテンツ強制クローズ';
    protected static ?int $navigationSort = MaintenanceDisplayOrder::MST_ADVENT_BATTLE_CLOSE_DISPLAY_ORDER->value; // メニューの並び順

    public static function table(Table $table): Table
    {
        $now = CarbonImmutable::now()->setSecond(0);

        return $table
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                IconColumn::make('is_valid')
                    ->label('有効')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('ステータス')
                    ->state(function (MngContentClose $mngContentClose) use ($now) {
                        if (!$mngContentClose){
                            return 'クローズ設定なし';
                        }
                        return $mngContentClose->calcStatus($now);
                    })
                    ->sortable()
                    ->searchable()
                    ->badge(true)
                    ->color(function (MngContentClose $mngContentClose) use ($now) {
                        if (!$mngContentClose){
                            return 'gray';
                        }
                        return $mngContentClose->calcStatusBadgeColor($now);
                    }),
                TextColumn::make('content_type')
                    ->label('コンテンツタイプ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('content_id')
                    ->label('コンテンツID')
                    ->searchable()
                    ->sortable(),
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
                SelectFilter::make('is_valid')
                    ->label('有効フラグ')
                    ->options([
                        1 => '有効',
                        0 => '無効',
                    ]),
                SelectFilter::make('status')->label('ステータス')
                    ->options([
                        'before' => 'クローズ前',
                        'during' => 'クローズ中',
                        'after' => 'クローズ終了',
                    ])
                    ->query(fn (Builder $query, $data) => match ($data['value']) {
                        'before' => $query->where('is_valid', 1)->where('start_at', '>', $now),
                        'during' => $query->where('is_valid', 1)->where('start_at', '<=', $now)
                            ->where('end_at', '>=', $now),
                        'after' => $query->where('is_valid', 1)->where('end_at', '<', $now),
                        default => $query,
                    }),
                SelectFilter::make('validity_period')
                    ->form([
                        DateTimePicker::make('datetime')
                            ->label('有効日時'),
                    ])
                    ->label('有効日時')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['datetime'])) {
                            return $query;
                        }
                        return $query->where('is_valid', 1)
                            ->where('start_at', '<=', $data['datetime'])
                            ->where('end_at', '>=', $data['datetime']);
                    }),
                ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action->label('適用'),
            )
            ->actions(self::getActions(), position: ActionsPosition::BeforeColumns)
            ->emptyStateActions([
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('content_type')
                ->label('コンテンツタイプ')
                ->options(ContentMaintenanceType::getOptions())
                ->afterStateUpdated(function (callable $set, $state) {
                    if ($state !== ContentMaintenanceType::GACHA->value) {
                        $set('content_id', null);
                    }
                })
                ->live()
                ->required(),
            TextInput::make('content_id')
                ->label('コンテンツID')
                ->disabled(function (callable $get) {
                    return $get('content_type') !== ContentMaintenanceType::GACHA->value;
                })
                ->nullable(),
            DateTimePicker::make('start_at')
                ->label('開始日時')
                ->required(),
            DateTimePicker::make('end_at')
                ->label('終了日時')
                ->after('start_at')
                ->required(),
            Toggle::make('is_valid')
                ->label('有効フラグ')
                ->default(true)
                ->required(),
        ])->columns(1);
    }

    public static function getFormSchema(): array
    {
        return [
            Select::make('content_type')
                ->label('コンテンツタイプ')
                ->options(ContentMaintenanceType::getOptions())
                ->disabled(),
            TextInput::make('content_id')
                ->label('コンテンツID')
                ->disabled(function (callable $get) {
                    return $get('content_type') !== ContentMaintenanceType::GACHA->value;
                })
                ->nullable(),
            DateTimePicker::make('start_at')
                ->label('開始日時')
                ->disabled()
                ->required(),
            DateTimePicker::make('end_at')
                ->label('終了日時')
                ->after('start_at')
                ->required(),
            Toggle::make('is_valid')
                ->label('有効フラグ')
                ->default(true)
                ->required(),
        ];
    }

    public static function getActions(): array
    {
        return [
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make()
                ->after(function () {
                    /** @var \App\Domain\Resource\Mst\Repositories\MngContentCloseRepository $mngContentCloseRepository */
                    $mngContentCloseRepository = app(\App\Domain\Resource\Mst\Repositories\MngContentCloseRepository::class);
                    $mngContentCloseRepository->deleteAllCache();
                }),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMngContentCloses::route('/'),
            'create' => Pages\CreateMngContentClose::route('/create'),
            'edit' => Pages\EditMngContentClose::route('/{record}/edit'),
        ];
    }
}
