<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

use WonderPlanet\Domain\Currency\Utils\DBUtility as CurrencyDBUtility;

/**
 * admin DBレコードの基底クラス
 */
abstract class BaseAdminModel extends BaseUsrModel
{
    protected function getConnNameInternal(): string
    {
        return CurrencyDBUtility::getAdminConnName();
    }
}
