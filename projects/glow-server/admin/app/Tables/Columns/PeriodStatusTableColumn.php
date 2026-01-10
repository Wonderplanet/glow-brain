<?php

namespace App\Tables\Columns;

use App\Constants\PeriodStatus;
use App\Entities\Clock;
use Filament\Tables\Columns\TextColumn;

/**
 * 開催ステータスのテーブルカラム
 */
class PeriodStatusTableColumn extends TextColumn
{
    /**
     * 開催期間の列名がstart_at,end_at以外のテーブルがあるので、その場合は変更する
     */
    private string $startAtColumnName = 'start_at';
    private string $endAtColumnName = 'end_at';

    protected function setUp(): void
    {
        parent::setUp();

        $clock = app(Clock::class);
        $now = $clock->now();

        $this->state(function ($record) use ($now) {
                $startAt = $record->{$this->startAtColumnName};
                $endAt = $record->{$this->endAtColumnName};

                if ($now->between($startAt, $endAt)) {
                    return PeriodStatus::DURING->label();
                }
                if ($now->lt($startAt)) {
                    return PeriodStatus::BEFORE->label();
                }
                return PeriodStatus::ENDED->label();
            })
            ->badge(true)
            ->color(function ($record) use ($now) {
                $startAt = $record->{$this->startAtColumnName};
                $endAt = $record->{$this->endAtColumnName};

                if ($now->between($startAt, $endAt)) {
                    return PeriodStatus::DURING->badgeColor();
                }
                if ($now->lt($startAt)) {
                    return PeriodStatus::BEFORE->badgeColor();
                }
                return PeriodStatus::ENDED->badgeColor();
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
}
