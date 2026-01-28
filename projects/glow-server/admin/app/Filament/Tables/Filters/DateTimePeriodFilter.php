<?php

declare(strict_types=1);

namespace App\Filament\Tables\Filters;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * 現在期間中のデータを表示するフィルター
 * 期間開始終了日時のカラム名を指定することで、現在日時を元に期間中のデータのみを表示する
 * 現在日時は呼び出し側で設定することも可能
 *
 * 入力された日時はJSTと解釈されて、DB側のUTCに変換されて検索される
 */
class DateTimePeriodFilter extends BaseColumnFilter
{
    // 期間開始日時のカラム名
    private string $fromColumnName;

    // 期間終了日時のカラム名
    private string $toColumnName;

    // 期間の基になる日時
    private CarbonImmutable $nowJst;

    /**
     * フィルタを設定
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // nowJstが指定されてなければ現在時刻を設定する
        $this->nowJst = $this->nowJst ?? CarbonImmutable::now();

        // nowJstの日時を元に、期間中であるデータをフィルタリング
        $this
            ->query(function (Builder $query) : Builder {
                $nowUtc = $this->nowJst->utc();
                return $query->where($this->fromColumnName, '<=', $nowUtc)
                    ->where($this->toColumnName, '>=', $nowUtc);
            });
    }

    /**
     * 期間開始日時のカラム名を指定
     *
     * @param string $fromColumnName
     * @return $this
     */
    public function setFromColumnName(string $fromColumnName): self
    {
        $this->fromColumnName = $fromColumnName;

        return $this;
    }

    /**
     * 期間終了日時のカラム名を指定
     *
     * @param string $toColumnName
     * @return $this
     */
    public function setToColumnName(string $toColumnName): self
    {
        $this->toColumnName = $toColumnName;

        return $this;
    }

    /**
     * 指定期間の基になる日時をJSTで指定
     * 指定しなかった場合は現在時刻となる
     *
     * @param CarbonImmutable $carbonImmutableJst
     * @return $this
     */
    public function setNow(CarbonImmutable $carbonImmutableJst): self
    {
        $this->nowJst = $carbonImmutableJst;

        return $this;
    }
}
