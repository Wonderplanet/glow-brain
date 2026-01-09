<?php

declare(strict_types=1);

namespace App\Infolists\Components;

use App\Constants\SystemConstants;
use Filament\Infolists\Components\TextEntry;

/**
 * 日時を表示するためのエントリ
 */
class DateTimeEntry extends TextEntry
{
    /**
     * 初期設定
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->timezone(SystemConstants::VIEW_TIMEZONE)
            ->dateTime('Y/m/d H:i:s');
    }
}
