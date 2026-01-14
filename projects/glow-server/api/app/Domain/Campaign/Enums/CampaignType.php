<?php

declare(strict_types=1);

namespace App\Domain\Campaign\Enums;

enum CampaignType: string
{
    // スタミナ
    case STAMINA = 'Stamina';
    // 経験値
    case EXP = 'Exp';
    // 原画のかけら
    case ARTWORK_FRAGMENT = 'ArtworkFragment';
    // アイテムドロップ
    case ITEM_DROP = 'ItemDrop';
    // コインドロップ
    case COIN_DROP = 'CoinDrop';
    // 挑戦回数
    case CHALLENGE_COUNT = 'ChallengeCount';
}
