<?php

namespace App\Infolists\Components;

use App\Models\Mst\IAssetImage;
use Filament\Infolists\Components\Entry;

class AssetBgmEntry extends Entry
{
    protected string $view = 'infolists.components.asset-bgm-entry';
    
    public function makeAssetPath(): ?string
    {
        $record = $this->getState();
        return $record;
    }

    public function makeBgPath(): ?string
    {
        $record = $this->getState();

        if (!($record instanceof IAssetImage)) {
            return null;
        }

        return $record->makeBgPath();
    }
}
