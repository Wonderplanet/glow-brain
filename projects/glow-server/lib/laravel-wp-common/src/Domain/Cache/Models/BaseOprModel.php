<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Cache\Models;

use WonderPlanet\Domain\Common\Models\BaseModel;
use WonderPlanet\Domain\Common\Utils\DBUtility;

/**
 * opr DBレコードの基底クラス
 */
abstract class BaseOprModel extends BaseModel
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
     *
     * @var boolean
     */
    public $timestamps = true;

    protected function getConnNameInternal(): string
    {
        // mst DBと同じコネクションを使用する
        return DBUtility::getMstConnName();
    }
}
