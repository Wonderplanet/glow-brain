<?php

declare(strict_types=1);

namespace App\Domain\Resource\Enums;

/**
 * 図鑑新着バッチ(is_new_encyclopedia)の状態
 */
enum EncyclopediaCollectStatus: int
{
    case IS_NEW = 1;
    case IS_NOT_NEW = 0;
}
