<?php

namespace App\Tables\Columns;

use App\Constants\SystemConstants;
use Filament\Tables\Columns\TextColumn;

/**
 * 開催期間カラム
 */
class PeriodColumn extends TextColumn
{
    protected string $view = 'tables.columns.period-column';

    /**
     * 開催期間の列名がstart_at,end_at以外のテーブルがあるので、その場合は変更する
     */
    private string $startAtColumnName = 'start_at';
    private string $endAtColumnName = 'end_at';

    /**
     * 開催期間
     */
    private string $duration = '';

    protected function setUp(): void
    {
        parent::setUp();

        // Recordにアクセスして初期化
        $this->getStateUsing(function ($record) {
            $this->duration = $this->calcDuration();
        });
    }

    public function startAtColumnName(string $startAtColumnName): self
    {
        $this->startAtColumnName = $startAtColumnName;

        return $this;
    }

    public function endAtColumnName(string $endAtColumnName): self
    {
        $this->endAtColumnName = $endAtColumnName;

        return $this;
    }

    public function isValid(): bool
    {
        $record = $this->record;

        return isset($record->{$this->startAtColumnName}) && isset($record->{$this->endAtColumnName});
    }

    public function getStartAt(): string
    {
        if (!$this->isValid()) {
            return '';
        }

        return $this->record->{$this->startAtColumnName}->format(SystemConstants::VIEW_DATETIME_FORMAT);
    }

    public function getEndAt(): string
    {
        if (!$this->isValid()) {
            return '';
        }

        return $this->record->{$this->endAtColumnName}->format(SystemConstants::VIEW_DATETIME_FORMAT);
    }

    public function calcDuration(): string
    {
        if (!$this->isValid()) {
            return '';
        }

        $record = $this->record;

        $duration = $record->start_at->diff($record->end_at);
        return $duration->format('%a日 %h時間 %i分');
    }
}
