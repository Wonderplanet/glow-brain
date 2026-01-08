<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

use WonderPlanet\Domain\Currency\Models\BaseModel;
use WonderPlanet\Domain\Currency\Utils\DBUtility as CurrencyDBUtility;

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
     * created_at,updated_atを使用しない
     *
     * @var boolean
     */
    public $timestamps = false;

    protected function getConnNameInternal(): string
    {
        // mst DBと同じコネクションを使用する
        return CurrencyDBUtility::getMstConnName();
    }
}
