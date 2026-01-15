<?php

namespace App\Infolists\Components;

use Filament\Infolists\Components\Entry;

class AssetKomaImageEntry extends Entry
{
    protected string $view = 'infolists.components.asset-image-entry';
    const ASSET_IMAGE_WIDTH = 300;

    public function makeAssetPath(): ?string
    {
        $record = $this->getState();
        return $record;
    }

    public function makeBgPath(): ?string
    {
        return null;
    }

    public function makeWidth(): int
    {
        return self::ASSET_IMAGE_WIDTH;
    }
 
    public function getAssetKey(): ?string
    {
        $record = $this->getRecord();

        if (!$record) {
            return null;
        }

        return $record->asset_key;
    }
}
