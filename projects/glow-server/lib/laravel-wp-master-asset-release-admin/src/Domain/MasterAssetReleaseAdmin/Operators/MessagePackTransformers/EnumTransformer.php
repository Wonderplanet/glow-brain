<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\MessagePackTransformers;

use MessagePack\CanPack;
use MessagePack\Packer;

class EnumTransformer implements CanPack
{
    public function pack(Packer $packer, $value) : ?string
    {
        return $value instanceof \UnitEnum
            ? $packer->packStr($value->value)
            : null;
    }
}
