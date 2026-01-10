<?php

namespace App\Infolists\Components;

use App\Models\Mst\IAssetImage;
use Filament\Infolists\Components\Entry;

class AssetImageEntry extends Entry
{
    protected string $view = 'infolists.components.asset-image-entry';
    const ASSET_IMAGE_WIDTH = 50;
    
    public function makeAssetPath(): ?string
    {
        $record = $this->getState();

        if (!($record instanceof IAssetImage)) {
            return null;
        }

        return $record->makeAssetPath();
    }

    public function makeBgPath(): ?string
    {
        $record = $this->getState();

        if (!($record instanceof IAssetImage)) {
            return null;
        }

        return $record->makeBgPath();
    }

    public function makeWidth(): int
    {
        $record = $this->getState();

        if (!($record instanceof IAssetImage)) {
            return 0;
        }
        return self::ASSET_IMAGE_WIDTH;
    }
    
    public function getAssetKey(): ?string
    {
        $record = $this->getRecord();

        if (!($record instanceof IAssetImage)) {
            return null;
        }

        return $record->asset_key;
    }
}
