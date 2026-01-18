<?php

declare(strict_types=1);

namespace App\Traits;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Tables\Columns\RewardInfoColumn;

trait UserResourceLogUnitTrait
{
    protected function getResourceLogUnitUpColumns($rewardInfos): array
    {
        return [
            TextColumn::make('nginx_request_id')
                ->label('APIリクエストID')
                ->searchable()
                ->sortable(),
            RewardInfoColumn::make('resource_id')
                ->label('キャラ情報')
                ->getStateUsing(
                    function ($record) use ($rewardInfos) {
                        return $rewardInfos->get($record->mst_unit_id);
                    }
                ),
            ];
    }

    protected function getResourceLogUnitUpFilters(): array
    {
        return [
            Filter::make('nginx_request_id')
                ->form([
                    TextInput::make('nginx_request_id')
                        ->label('APIリクエストID')
                ])
                ->query(function (Builder $query, array $data): Builder {
                    if (blank($data['nginx_request_id'])) {
                        return $query;
                    }
                    return $query->where('nginx_request_id', 'like', "{$data['nginx_request_id']}%");
                }),
            Filter::make('mst_unit_id')
                ->form([
                    TextInput::make('mst_unit_id')
                        ->label('キャラ情報')
                ])
                ->query(function (Builder $query, array $data): Builder {
                    if (blank($data['mst_unit_id'])) {
                        return $query;
                    }
                    return $query->where('mst_unit_id', 'like', "%{$data['mst_unit_id']}%");
                }),
        ];
    }
}
