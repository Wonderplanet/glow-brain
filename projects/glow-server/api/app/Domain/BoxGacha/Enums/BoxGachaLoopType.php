<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\Enums;

/**
 * BOXガチャのループタイプ
 *
 * ALL: 全てのBOXレベルをループ（1箱目→2箱目→...→N箱目→1箱目→...）
 * LAST: 最後のBOXレベルのみループ（1箱目→2箱目→...→N箱目→N箱目→...）
 * FIRST: 最終BOXを引ききったら1箱目に戻り、以降1箱目をループ
 */
enum BoxGachaLoopType: string
{
    case ALL = 'All';
    case LAST = 'Last';
    case FIRST = 'First';
}
