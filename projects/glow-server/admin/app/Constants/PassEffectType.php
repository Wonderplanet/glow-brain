<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Shop\Enums\PassEffectType as ApiPassEffectType;
use Illuminate\Support\Collection;

enum PassEffectType: string
{
    case IDLE_INCENTIVE_ADD_REWARD = ApiPassEffectType::IDLE_INCENTIVE_ADD_REWARD->value;
    case IDLE_INCENTIVE_MAX_QUICK_RECEIVE_BY_DIAMOND = ApiPassEffectType::IDLE_INCENTIVE_MAX_QUICK_RECEIVE_BY_DIAMOND->value;
    case IDLE_INCENTIVE_MAX_QUICK_RECEIVE_BY_AD = ApiPassEffectType::IDLE_INCENTIVE_MAX_QUICK_RECEIVE_BY_AD->value;
    case STAMINA_ADD_RECOVERY_LIMIT = ApiPassEffectType::STAMINA_ADD_RECOVERY_LIMIT->value;
    case AD_SKIP = ApiPassEffectType::AD_SKIP->value;
    case CHANGE_BATTLE_SPEED = ApiPassEffectType::CHANGE_BATTLE_SPEED->value;

    public function label(): string
    {
        return match ($this) {
            self::IDLE_INCENTIVE_ADD_REWARD => '探索報酬増加',
            self::IDLE_INCENTIVE_MAX_QUICK_RECEIVE_BY_DIAMOND => '探索クイック受取上限回数増加(プリズム)',
            self::IDLE_INCENTIVE_MAX_QUICK_RECEIVE_BY_AD => '探索クイック受取上限回数増加(広告視聴)',
            self::STAMINA_ADD_RECOVERY_LIMIT => 'スタミナ上限増加',
            self::AD_SKIP => '広告スキップ',
            self::CHANGE_BATTLE_SPEED => 'バトルスピード変更',
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

    public function labelWithDetail(int $effectValue): string
    {
        $detail = $this->detail($effectValue);

        return sprintf('%s(%s)', $this->label(), $detail);
    }

    public function detail(int $effectValue): string
    {
        return match ($this) {
            self::IDLE_INCENTIVE_ADD_REWARD => $this->formatMultiplier($effectValue),
            self::IDLE_INCENTIVE_MAX_QUICK_RECEIVE_BY_DIAMOND => $this->formatAddition($effectValue),
            self::IDLE_INCENTIVE_MAX_QUICK_RECEIVE_BY_AD => $this->formatAddition($effectValue),
            self::STAMINA_ADD_RECOVERY_LIMIT => $this->formatAddition($effectValue),
            self::AD_SKIP => $this->formatRelease(),
            self::CHANGE_BATTLE_SPEED => $this->formatRelease(),
        };
    }

    private function formatMultiplier(int $effectValue): string
    {
        return "{$effectValue}倍";
    }

    private function formatAddition(int $effectValue): string
    {
        $value = $effectValue > 0 ? "+{$effectValue}" : "{$effectValue}";
        return "{$value}";
    }

    private function formatRelease(): string
    {
        return '機能解放';
    }
}
