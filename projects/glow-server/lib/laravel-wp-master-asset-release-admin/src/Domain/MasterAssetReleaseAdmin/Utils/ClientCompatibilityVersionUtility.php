<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Utils;

use Closure;

class ClientCompatibilityVersionUtility
{
    /**
     * 入力されたクライアント互換性バージョンのバリデーションを実行するコールバック関数を作成
     *
     * @param string|null $maxClientVersion
     * @return callable
     */
    public static function makeValidateClientCompatibilityVersion(string|null $maxClientVersion): callable
    {
        return function (string $attribute, $value, Closure $fail) use ($maxClientVersion) {
            if (is_null($maxClientVersion)) {
                return;
            }

            if (version_compare($maxClientVersion, $value, '>')) {
                // 入力値が、登録されているクライアントバージョンの最大値よりも小さい場合は登録できない
                $fail("最新バージョン({$maxClientVersion})より大きいバージョンまたは同一バージョンを指定してください");
            }
        };
    }
}
