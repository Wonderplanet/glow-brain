<?php

namespace App\Filament\Traits;

use Filament\Actions;

trait MessageListTrait
{
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
