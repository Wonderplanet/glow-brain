<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\MessagePackTransformers;

use Carbon\Carbon;
use MessagePack\CanPack;
use MessagePack\Packer;

class CarbonTransformer implements CanPack
{
    public function pack(Packer $packer, $value) : ?string
    {
        return $value instanceof Carbon
            ? $packer->packStr($value->toISOString())
            : null;
    }
}
