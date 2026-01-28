<?php

namespace App\Infolists\Components;

use Filament\Infolists\Components\TextEntry;

class LineBreakTextEntry extends TextEntry
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('説明')
            ->listWithLineBreaks()
            ->separator('\n');
    }
}
