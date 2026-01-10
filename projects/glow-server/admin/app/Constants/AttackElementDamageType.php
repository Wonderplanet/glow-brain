<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;

/**
 * 画面遷移先として設定できる遷移先の種別   
 */
enum AttackElementDamageType: string
{
    // 遷移先なし
    case NONE = 'None';

    // ダメージ
    case DAMAGE = 'Damage';

    // 毒ダメージ
    case POISIN_DAMAGE = 'PoisonDamege';

    //燃焼ダメージ
    case BURN_DAMAGE = 'BurnDamege';

    // スリップダメージ
    case SLIP_DAMAGE = 'SlipDamege';

    // 回復
    case HEAL = 'Heal';

    public function label(): string
    {
        return match ($this) {
            self::NONE => 'なし',
            self::DAMAGE => 'ダメージ',
            self::POISIN_DAMAGE => '毒ダメージ',
            self::BURN_DAMAGE => '燃焼ダメージ',
            self::SLIP_DAMAGE => 'スリップダメージ',
            self::HEAL => '回復',
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
