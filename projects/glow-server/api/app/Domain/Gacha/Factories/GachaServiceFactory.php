<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Factories;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Services\Draw\GachaDrawService;
use App\Domain\Gacha\Services\Draw\StandardGachaDrawService;
use App\Domain\Gacha\Services\Draw\StepupGachaDrawService;

/**
 * ガチャ抽選サービスのファクトリ
 */
class GachaServiceFactory
{
    public function __construct()
    {
    }

    /**
     * ガチャタイプに応じたガチャ抽選サービスを取得する
     *
     * @param string $gachaType
     * @return GachaDrawService
     * @throws GameException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getGachaDrawService(string $gachaType): GachaDrawService
    {
        return match ($gachaType) {
            GachaType::STEPUP->value => app()->make(StepupGachaDrawService::class),
            default => app()->make(StandardGachaDrawService::class),
        };
    }
}
