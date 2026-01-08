<?php

namespace App\Filament\Resources;

use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Filament\Pages\MstExchangeDetail;
use App\Filament\Resources\MstExchangeResource\Pages;
use App\Models\Mst\MstExchange;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MstExchangeResource extends Resource
{
    protected static ?string $model = MstExchange::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $modelLabel = '交換所';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::EXCHANGE_DISPLAY_ORDER->value;

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
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_exchange_i18n.name')
                    ->label('交換所名')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lineup_group_id')
                    ->label('ラインナップグループID')
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
                Filter::make('id')
                    ->form([
                        TextInput::make('id')
                            ->label('ID')
                    ])
                    ->label('ID')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id'])) {
                            return $query;
                        }
                        return $query->where('id', 'like', "%{$data['id']}%");
                    }),
                Filter::make('name')
                    ->form([
                        TextInput::make('name')
                            ->label('交換所名')
                    ])
                    ->label('交換所名')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['name'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_exchange_i18n', function ($query) use ($data) {
                            $query->where('name', 'like', "%{$data['name']}%");
                        });
                    }),
                Filter::make('lineup_group_id')
                    ->form([
                        TextInput::make('lineup_group_id')
                            ->label('ラインナップグループID')
                    ])
                    ->label('ラインナップグループID')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['lineup_group_id'])) {
                            return $query;
                        }
                        return $query->where('lineup_group_id', 'like', "%{$data['lineup_group_id']}%");
                    }),
                Filter::make('duration')
                    ->form([
                        DateTimePicker::make('datetime')
                                ->label('有効日時'),
                    ])
                    ->label('有効日時')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['datetime'])) {
                            return $query;
                        }
                        return $query->where('start_at', '<=', $data['datetime'])
                            ->where(function ($query) use ($data) {
                                $query->whereNull('end_at')
                                    ->orWhere('end_at', '>=', $data['datetime']);
                            });
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
                        return MstExchangeDetail::getUrl([
                            'mstExchangeId' => $record->id,
                        ]);
                    }),
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
            'index' => Pages\ListMstExchanges::route('/'),
        ];
    }
}
