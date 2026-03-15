<?php

declare(strict_types=1);

namespace App\Domain\Item\Enums;

use Illuminate\Support\Collection;

enum ItemType: string
{
    // キャラのかけら
    case CHARACTER_FRAGMENT = 'CharacterFragment';
    // リミテッドメモリー
    case RANK_UP_MATERIAL = 'RankUpMaterial';
    // メモリーフラグメント
    case RANK_UP_MEMORY_FRAGMENT = 'RankUpMemoryFragment';
    // ステージメダル
    case STAGE_MEDAL = 'StageMedal';
    // コイン放置BOX
    case IDLE_COIN_BOX = 'IdleCoinBox';
    // リミテッドメモリー放置BOX
    case IDLE_RANK_UP_MATERIAL_BOX = 'IdleRankUpMaterialBox';
    // ランダムかけらBOX
    case RANDOM_FRAGMENT_BOX = 'RandomFragmentBox';
    // 選択かけらBOX
    case SELECTION_FRAGMENT_BOX = 'SelectionFragmentBox';
    // ガチャチケット
    case GACHA_TICKET = 'GachaTicket';
    // ガシャメダル(ジャンプラメダル)
    case GACHA_MEDAL = 'GachaMedal';
    // スタミナ回復アイテム（パーセンテージ指定）
    case STAMINA_RECOVERY_PERCENT = 'StaminaRecoveryPercent';
    // スタミナ回復アイテム（固定値指定）
    case STAMINA_RECOVERY_FIXED = 'StaminaRecoveryFixed';
    // その他
    case ETC = 'Etc';
    // 原画グレードアップ素材
    case ARTWORK_GRADE_UP_MATERIAL = 'ArtworkGradeUpMaterial';
    // 原画グレードアップシリーズ専用素材
    case ARTWORK_GRADE_UP_SERIES_MATERIAL = 'ArtworkGradeUpSeriesMaterial';

    public function label(): string
    {
        return match ($this) {
            self::CHARACTER_FRAGMENT => 'キャラのかけら',
            self::RANK_UP_MATERIAL => 'リミテッドメモリー',
            self::RANK_UP_MEMORY_FRAGMENT => 'メモリーフラグメント',
            self::STAGE_MEDAL => 'ステージメダル',
            self::IDLE_COIN_BOX => 'コイン放置BOX',
            self::IDLE_RANK_UP_MATERIAL_BOX => 'リミテッドメモリー放置BOX',
            self::RANDOM_FRAGMENT_BOX => 'ランダムかけらBOX',
            self::SELECTION_FRAGMENT_BOX => '選択かけらBOX',
            self::GACHA_TICKET => 'ガシャチケット',
            self::GACHA_MEDAL => 'ジャンプラメダル',
            self::STAMINA_RECOVERY_PERCENT => 'スタミナボトル',
            self::STAMINA_RECOVERY_FIXED => 'スタミナドリンク',
            self::ETC => 'その他',
            self::ARTWORK_GRADE_UP_MATERIAL => '原画グレードアップ素材',
            self::ARTWORK_GRADE_UP_SERIES_MATERIAL => '原画グレードアップシリーズ専用素材',
        };
    }

    public static function labels(): Collection
    {
        $cases = self::cases();
        $labels = collect();
        foreach ($cases as $case) {
            $labels->put($case->value, $case->label());
        }
        return $labels;
    }
}
