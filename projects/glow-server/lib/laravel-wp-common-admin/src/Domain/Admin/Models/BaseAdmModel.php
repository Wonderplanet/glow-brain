<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Admin\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use WonderPlanet\Domain\Common\Models\BaseModel;
use WonderPlanet\Domain\Common\Utils\DBUtility;

/**
 * adm DBレコードの基底クラス
 */
abstract class BaseAdmModel extends BaseModel
{
    use HasUuids;

    /**
     * @var boolean
     */
    public $timestamps = true;

    /**
     * コネクション名を指定
     *
     * @return string
     */
    protected function getConnNameInternal(): string
    {
        return DBUtility::getAdminConnName();
    }
}
