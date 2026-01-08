<?php

declare(strict_types=1);

namespace App\Utils;

use App\Domain\Common\Utils\StringUtil as BaseStringUtil;
use Illuminate\Support\Str;

class StringUtil extends BaseStringUtil
{
    /**
     * 指定された複数の文字列を連結して、パスを生成する
     *
     * @param string[] $paths
     * @return string
     */
    public static function joinPath(string ...$paths): string
    {
        $convertedPaths = [];
        foreach ($paths as $path) {
            $path = rtrim($path, '/');
            if ($path === '') {
                continue;
            }
            $convertedPaths[] = $path;
        }

        return implode('/', $convertedPaths);
    }

    public static function makeIdNameViewString(string $id, string $name): string
    {
        return sprintf('[%s] %s', $id, $name);
    }

    public static function generateUniqueId(): string
    {
        return Str::uuid()->toString();
    }

    public static function getLevelDirName(string $path, int $level = 1): ?string
    {
        $path = trim($path, '/');
        $pathArray = explode('/', $path);

        $dirName = $pathArray[$level - 1] ?? null;
        if ($dirName === null) {
            return null;
        }

        if (pathinfo($dirName, PATHINFO_EXTENSION) === '') {
            return $dirName;
        }

        return null;
    }

    /**
     * DB名をフィルタリング
     * mstDBとoprDBは、リリースキーとハッシュ値がついてる部分を削除する
     * 例: qa_mst_リリースキー_dbハッシュ... -> qa_mst
     *
     * @param string $dbType DB種別 (mst, opr, usr, log, adm)
     * @param string $dbName フィルタリング対象のDB名
     * @return string フィルタリング後のDB名
     */
    public static function filterDbName(string $dbType, string $dbName): string
    {
        // mstDBとoprDBは、ハッシュ値がついてる部分を削除する
        if ($dbType === 'mst' || $dbType === 'opr') {
            return preg_replace('/_[0-9]+_[a-f0-9]{32}$/', '', $dbName);
        }
        return $dbName;
    }
}
