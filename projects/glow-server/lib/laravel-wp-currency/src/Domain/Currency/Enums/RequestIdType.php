<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Enums;

/**
 * リクエストIDの種類
 */
enum RequestIdType: string
{
    /**
     * プログラム内で生成されたID
     */
    case Gen = 'Gen';
    /**
     * リクエストから取得したID
     * 具体的にはnginxのrequest_idが入る
     */
    case Request = 'Req';
    /**
     * プロダクトで指定したID
     */
    case Product = 'Product';
}
