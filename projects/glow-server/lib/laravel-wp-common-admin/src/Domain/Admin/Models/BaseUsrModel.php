<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Admin\Models;

use WonderPlanet\Domain\Common\Models\BaseModel;
use WonderPlanet\Domain\Common\Utils\DBUtility;

/**
 * usr DBレコードの基底クラス
 */
abstract class BaseUsrModel extends BaseModel
{
    /**
     * 主キーが整数でないため、自動incrementを無効化する
     *
     * @var boolean
     */
    public $incrementing = false;

    /**
     * 主キーの型をstringにする
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * IDカラムを使用するか
     *
     * falseの場合、idカラムを自動生成しない
     *
     * @var boolean
     */
    protected $useUniqueId = true;

    protected function getConnNameInternal(): string
    {
        return DBUtility::getUsrConnName();
    }

    protected static function booting()
    {
        static::creating(function ($model) {
            // idが入っていなかった場合、UUIDを生成して入れる
            // 基本的にすべてのレコードにUUIDがついているので共通化
            // もし不要なテーブルが出てきた場合、それを除外する
            if ($model->useUniqueId && blank($model->id)) {
                $model->id = self::generateId();
            }
        });
    }

    /**
     * ID用のUUIDを生成する
     *
     * bluk insertにより外部でもIDを生成する必要があり、
     * 処理の共通化のためpublicにしている
     *
     * @return string
     */
    public static function generateId(): string
    {
        return DBUtility::generateUUID();
    }
}
