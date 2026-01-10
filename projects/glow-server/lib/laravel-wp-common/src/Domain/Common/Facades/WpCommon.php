<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Facades;

/**
 * リクエストIDを管理するFacade
 *
 * @method static string getApiRequestId() APIリクエストごとにユニークになるIDを取得する
 * @method static string getClientRequestId() クライアントが指定したリクエストIDを取得する
 * @method static string getFrontRequestId() PHPのフロントにあるミドルウェアのリクエストIDを取得する
 *
 * @see \WonderPlanet\Domain\Common\Delegators\WpCommonDelegator
 */
class WpCommon extends BaseFacade
{
    public const FACADE_ACCESSOR = 'laravel-wp-common-facade';
}
