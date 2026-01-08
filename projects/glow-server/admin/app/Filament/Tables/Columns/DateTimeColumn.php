<?php

declare(strict_types=1);

namespace App\Filament\Tables\Columns;

use App\Constants\SystemConstants;
use Filament\Tables\Columns\TextColumn;

/**
 * $tableでDateTimeを表示するカラム
 */
class DateTimeColumn extends TextColumn
{
    /**
     * 日時のフォーマットを設定
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->timezone(SystemConstants::VIEW_TIMEZONE)
            ->dateTime('Y/m/d H:i:s');
    }
}
