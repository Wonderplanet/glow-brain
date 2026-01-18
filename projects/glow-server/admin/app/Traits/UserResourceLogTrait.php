<?php

declare(strict_types=1);

namespace App\Traits;

use Filament\Tables\Columns\TextColumn;
use App\Constants\LogTablePageConstants;
use App\Constants\LogResourceActionType;
use App\Tables\Columns\LogTriggerValueColumn;
use App\Constants\LogResourceTriggerSource;
use Filament\Tables\Filters\SelectFilter;

trait UserResourceLogTrait
{
    use UserLogTableFilterTrait;

    protected function getResourceLogColumns(): array
    {
        return [
            TextColumn::make('nginx_request_id')
                ->label('APIリクエストID')
                ->searchable()
                ->sortable(),
            TextColumn::make('action_type')
                ->label('獲得/消費')
                ->getStateUsing(
                    function ($record) {
                        $actionType = LogResourceActionType::tryFrom($record->action_type);
                        return $actionType->label();
                    }
                )
                ->searchable()
                ->sortable(),
            TextColumn::make('before_amount')
                ->label('変動前の量')
                ->searchable()
                ->sortable(),
            TextColumn::make('after_amount')
                ->label('変動後の量')
                ->searchable()
                ->sortable(),
            TextColumn::make('change_amount')
                ->label('獲得/消費変動量')
                ->getStateUsing(
                    function ($record) {
                        $changeAmount = $record->after_amount - $record->before_amount;
                        if (LogResourceActionType::GET->value === $record->action_type) {
                            return '+' . $changeAmount;
                        }
                        return $changeAmount;
                    }
                )
                ->searchable()
                ->sortable(),
            TextColumn::make('trigger_source')
                ->label('経緯情報ソース')
                ->getStateUsing(
                    function ($record) {
                        if ($record?->trigger_source !== null) {
                            $triggerSource = LogResourceTriggerSource::tryFrom($record?->trigger_source);
                            if ($triggerSource !== null) {
                                return $triggerSource->label();
                            }
                        }
                        return $record->trigger_source;
                    }
                )
                ->searchable()
                ->sortable(),
            LogTriggerValueColumn::make('trigger_value')
                ->label('経緯情報値')
                ->getStateUsing(
                    function ($record) {
                        return $record->log_trigger_info ?? null;
                    })
                ->searchable()
                ->sortable(),
            TextColumn::make('trigger_option')
                ->label('経緯情報オプション')
                ->searchable()
                ->sortable(),
            TextColumn::make('created_at')
                ->label('獲得/消費日時')
                ->searchable()
                ->sortable(),
        ];
    }

    protected function getResourceLogFilters(): array
    {
        return array_merge(
            $this->getCommonLogFilters([LogTablePageConstants::CREATED_AT_RANGE, LogTablePageConstants::NGINX_REQUEST_ID]),
            [
                SelectFilter::make('action_type')
                    ->label('獲得/消費')
                    ->options(LogResourceActionType::labels()),
            ]
        );
    }
}
