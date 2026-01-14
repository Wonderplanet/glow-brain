<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Admin\Filament\Tables\Columns;

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

        $this->timezone(config('wp_common_admin.view_time_zone'))
            ->dateTime('Y/m/d H:i:s');
    }
}
