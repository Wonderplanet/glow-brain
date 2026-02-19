<?php

namespace App\Tables\Filters;

use App\Constants\PeriodStatus;
use App\Entities\Clock;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * 開催ステータスのテーブルフィルタ
 */
class PeriodStatusTableSelectFilter extends SelectFilter
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

        $this->options(PeriodStatus::labels())
            ->query(function (Builder $query, array $data) use ($now): Builder {
                if (blank($data['value'])) {
                    return $query;
                }

                switch ($data['value']) {
                    case PeriodStatus::BEFORE->value:
                        // 開催前
                        return $query->where($this->startAtColumnName, '>', $now);
                    case PeriodStatus::DURING->value:
                        // 開催中
                        return $query->where($this->startAtColumnName, '<=', $now)
                            ->where($this->endAtColumnName, '>=', $now);
                    case PeriodStatus::ENDED->value:
                        // 終了
                        return $query->where($this->endAtColumnName, '<', $now);
                    default:
                        return $query;
                }
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
