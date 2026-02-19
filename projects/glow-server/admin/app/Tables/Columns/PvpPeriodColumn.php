<?php

namespace App\Tables\Columns;

use App\Constants\SystemConstants;
use Carbon\CarbonImmutable;
use Filament\Tables\Columns\TextColumn;

/**
 * 開催期間カラム
 */
class PvpPeriodColumn extends TextColumn
{
    /**
     * mst_pvps.idで期間が存在する場合のID文字数
     * “西暦4桁" . "0" . "週番号2桁"
     */
    private const int PVP_ID_LENGTH = 7;
    private const string PVP_START_HOUR = '12:00:00';
    private const string PVP_END_HOUR = '23:59:59';

    protected string $view = 'tables.columns.pvp-period-column';

    /**
     * 開催期間
     */
    private string $period = '';

    protected function setUp(): void
    {
        parent::setUp();

        // Recordにアクセスして初期化
        $this->getStateUsing(function ($record) {
            $this->period = $this->calcPeriod($record->id);
        });
    }

    public function getPeriod(): string
    {
        return $this->period;
    }

    private function calcPeriod(string $id): string
    {
        if (!is_numeric($id) || mb_strlen($id) !== self::PVP_ID_LENGTH) {
            // 週指定の設定ではないので開催期間はなし
            return '-';
        }

        $year = (int)substr($id, 0, 4);
        $week = (int)substr($id, 4);

        $startDay = CarbonImmutable::now()->setISODate($year, $week);
        $endDay = $startDay->copy()->addDays(6); // 週の終わりは7日目
        return $startDay->format("Y-m-d " . self::PVP_START_HOUR) . ' 〜 ' .
            $endDay->format("Y-m-d " . self::PVP_END_HOUR);
    }
}
