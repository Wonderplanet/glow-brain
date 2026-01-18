<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facadeの基底クラス
 */
abstract class BaseFacade extends Facade
{
    /**
     * Facadeにアクセスするための文字列
     *
     * scoped/singleton登録に使用する
     */
    public const FACADE_ACCESSOR = '';

    /**
     * デフォルトで使用するFacadeのアクセサを取得する
     * FACADE_ACCESSORを返すようにする
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return static::FACADE_ACCESSOR;
    }
}
