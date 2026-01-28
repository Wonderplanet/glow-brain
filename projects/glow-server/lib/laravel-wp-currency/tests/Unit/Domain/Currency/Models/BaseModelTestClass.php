<?php

namespace WonderPlanet\Tests\Unit\Domain\Currency\Models;

use WonderPlanet\Domain\Currency\Models\BaseModel;

/**
 * BaseModelTestのために作成したテスト用のモデル
 */
class BaseModelTestClass extends BaseModel
{
    /**
     * コネクション名を返す
     * 
     * テストにコネクションは使用しないため、空文字を返す
     *
     * @return string
     */
    protected function getConnNameInternal(): string
    {
        return '';
    }
}
