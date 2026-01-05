<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Constants;

class PlatformConstant
{
    // TODO: enumにする
    public const PLATFORM_IOS = 1;
    public const PLATFORM_ANDROID = 2;

    public const PLATFORM_STRING_LIST = [
        self::PLATFORM_IOS => 'iOS',
        self::PLATFORM_ANDROID => 'Android',
    ];
}
