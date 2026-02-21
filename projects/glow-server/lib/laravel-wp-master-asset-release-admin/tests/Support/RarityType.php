<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Support;

/**
 * テストで使用する為に作成
 */
enum RarityType: string
{
    /**
     * コモン
     */
    case Common = 'Common';

    /**
     * レア
     */
    case Rare = 'Rare';

    /**
     * エリート
     */
    case Elite = 'Elite';

    /**
     * エピック
     */
    case Epic = 'Epic';

    /**
     * レジェンド
     */
    case Legend = 'Legend';
}
