<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Utils;

use Wonderplanet\Domain\Common\Enums\Language;

/**
 * MasterDataに関するユーティリティクラス
 */
class MasterDataUtility
{
    /**
     * s3に配置するjsonファイルのリリースキー以下のファイルパスを取得
     * TODO: 開発環境で確認用のためjsonファイルをまだ生成しています。不要になった際にこちらも削除してください。
     *
     * @param string $target mstかoprのパスを持つ
     * @param string $hash
     * @return string
     */
    public static function getPath(string $target, string $hash): string
    {
        return "{$target}/{$target}_{$hash}.json";
    }

    /**
     * s3に配置するmessagePackファイルのリリースキー以下のファイルパスを取得
     * @param string $target
     * @param string $hash
     * @return string
     */
    public static function getPathOfMessagePack(string $target, string $hash): string
    {
        return "{$target}/{$target}_{$hash}.data";
    }

    /**
     * s3に配置するI18nのjsonファイルのリリースキー以下のパスを取得
     * TODO: 開発環境で確認用のためjsonファイルをまだ生成しています。不要になった際にこちらも削除してください。
     *
     * @param string $i18nPath
     * @param string $target mstかoprのパスを持つ
     * @param Language $language
     * @param string $hash
     * @return string
     */
    public static function getI18nPath(string $i18nPath, string $target, Language $language, string $hash): string
    {
        return "{$i18nPath}/{$target}_{$language->value}_{$hash}.json";
    }

    /**
     * s3に配置するI18nのmessagePackファイルのリリースキー以下のパスを取得
     *
     * @param string $i18nPath
     * @param string $target mstかoprのパスを持つ
     * @param Language $language
     * @param string $hash
     * @return string
     */
    public static function getI18nPathOfMessagePack(
        string $i18nPath,
        string $target,
        Language $language,
        string $hash
    ): string {
        return "{$i18nPath}/{$target}_{$language->value}_{$hash}.data";
    }
}
