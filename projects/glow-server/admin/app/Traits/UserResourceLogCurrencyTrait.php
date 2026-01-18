<?php

declare(strict_types=1);

namespace App\Traits;

use App\Constants\LogTablePageConstants;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;

trait UserResourceLogCurrencyTrait
{
    use UserLogTableFilterTrait;

    protected function getResourceLogCurrencyColumns(): array
    {
        return [
            TextColumn::make('trigger_type')
                ->label('変動契機')
                ->searchable()
                ->sortable(),
            TextColumn::make('trigger_id')
                ->label('変動契機ID')
                ->searchable()
                ->sortable(),
            TextColumn::make('trigger_name')
                ->label('変動契機名')
                ->searchable()
                ->sortable(),
            TextColumn::make('trigger_detail')
                ->label('そのほかの付与情報')
                ->searchable()
                ->sortable(),
            TextColumn::make('created_at')
                ->label('獲得/消費日時')
                ->searchable()
                ->sortable(),
        ];
    }

    protected function getResourceLogCurrencyFilters(): array
    {
        return array_merge(
            $this->getCommonLogFilters([
                LogTablePageConstants::CREATED_AT_RANGE,
            ]),
            [
                Filter::make('trigger_type')
                    ->form([
                        TextInput::make('trigger_type')
                            ->label('変動契機')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['trigger_type'])) {
                            return $query;
                        }
                        return $query->where('trigger_type', 'like', "{$data['trigger_type']}%");
                    }),
                Filter::make('trigger_id')
                    ->form([
                        TextInput::make('trigger_id')
                            ->label('変動契機ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['trigger_id'])) {
                            return $query;
                        }
                        return $query->where('trigger_id', 'like', "{$data['trigger_id']}%");
                    }),
                Filter::make('trigger_name')
                    ->form([
                        TextInput::make('trigger_name')
                            ->label('変動契機名')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['trigger_name'])) {
                            return $query;
                        }
                        return $query->where('trigger_name', 'like', "{$data['trigger_name']}%");
                    }),
                Filter::make('trigger_detail')
                    ->form([
                        TextInput::make('trigger_detail')
                            ->label('そのほかの付与情報')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['trigger_detail'])) {
                            return $query;
                        }
                        return $query->where('trigger_detail', 'like', "{$data['trigger_detail']}%");
                    }),
            ]
        );
    }
}
