<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

/**
 * log DBレコードの基底クラス
 *
 * 現在はusr DBのクラスと同じなので共通化している
 * 将来的に差異が出てきたら分けること
 */
abstract class BaseLogModel extends BaseUsrModel
{
}
