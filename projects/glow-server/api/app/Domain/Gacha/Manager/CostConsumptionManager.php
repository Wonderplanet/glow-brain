<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Manager;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Manager\Consumers\AdConsumer;
use App\Domain\Gacha\Manager\Consumers\CostConsumerInterface;
use App\Domain\Gacha\Manager\Consumers\DiamondConsumer;
use App\Domain\Gacha\Manager\Consumers\FreeConsumer;
use App\Domain\Gacha\Manager\Consumers\ItemConsumer;
use App\Domain\Gacha\Manager\Consumers\PaidDiamondConsumer;
use App\Domain\Gacha\Models\ILogGachaAction;
use App\Domain\Resource\Entities\CurrencyTriggers\GachaTrigger;

class CostConsumptionManager
{
    private ?CostConsumerInterface $costConsumer = null;

    /**
     * リソース消費に追加(※1種類しか消費しない想定なので設定は上書きされる)
     *
     * @param string       $usrUserId
     * @param ?string      $costId
     * @param int          $costNum
     * @param int          $platform
     * @param string       $billingPlatform
     * @param bool         $checkedAd
     * @param CostType     $costType
     * @param GachaTrigger $gachaTrigger
     *
     * @return void
     * @throws \Throwable
     */
    public function setConsumeResource(
        string $usrUserId,
        ?string $costId,
        int $costNum,
        int $platform,
        string $billingPlatform,
        bool $checkedAd,
        CostType $costType,
        GachaTrigger $gachaTrigger
    ): void {
        switch ($costType->value) {
            case CostType::ITEM->value:
                $this->costConsumer = app()->make(ItemConsumer::class);
                break;
            case CostType::DIAMOND->value:
                $this->costConsumer = app()->make(DiamondConsumer::class);
                break;
            case CostType::PAID_DIAMOND->value:
                $this->costConsumer = app()->make(PaidDiamondConsumer::class);
                break;
            case CostType::AD->value:
                $this->costConsumer = app()->make(AdConsumer::class);
                break;
            case CostType::FREE->value:
                $this->costConsumer = app()->make(FreeConsumer::class);
                break;
            default:
                throw new GameException(ErrorCode::GACHA_NOT_EXPECTED_COST, 'gacha not expected cost');
        }

        $this->costConsumer->setConsumeResource(
            $usrUserId,
            $costId,
            $costNum,
            $platform,
            $billingPlatform,
            $checkedAd,
            $gachaTrigger
        );
    }

    /**
     * リソース消費を実行
     *
     * @return void
     * @throws \Throwable
     */
    public function execConsumeResource(ILogGachaAction $logGachaAction): void
    {
        $this->costConsumer->execConsumeResource($logGachaAction);
    }
}
