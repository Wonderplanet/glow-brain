<?php

declare(strict_types=1);

namespace App\Utils;

class FormUtil
{
    public static function getUnsetOptionString(): string
    {
        return '__UNSET__';
    }

    public static function mergeUnsetOption(array $options): array
    {
        // 既存のオプションに未設定オプションを追加
        return [self::getUnsetOptionString() => '未設定'] + $options;
    }

    public static function isUnsetOptionString(mixed $value): bool
    {
        return $value === self::getUnsetOptionString();
    }

    /**
     * 未設定時を考慮してフォームデータから指定キーの値を取得する
     *
     * @param array $formData
     * @param string $key フォームデータのキー指定
     * @param mixed $default 未設定の場合のデフォルト値
     * @return mixed
     */
    public static function getKeyFromFormData(array $formData, string $key, mixed $default = null): mixed
    {
        $value = isset($formData[$key]) ? $formData[$key] : $default;

        if (self::isUnsetOptionString($value)) {
            return $default; // 未設定の場合はデフォルト値を返す
        }

        return $value; // 未設定でない場合はそのまま値を返す
    }
}
