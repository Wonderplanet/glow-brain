<?php

declare(strict_types=1);

namespace App\Domain\Common\Utils;

class SystemUtil
{
    /**
     * インフラ構築時に使用した環境名を取得する
     * @return string
     */
    public static function getInfraEnv(): string
    {
        $infraEnv = env('INFRA_ENV');
        if (StringUtil::isSpecified($infraEnv)) {
            return $infraEnv;
        }

        return env('APP_ENV');
    }
}
