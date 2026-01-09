<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Manager\Consumers;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Gacha\Models\ILogGachaAction;
use App\Domain\Item\Delegators\ItemDelegator;
use App\Domain\Resource\Entities\CurrencyTriggers\GachaTrigger;
use App\Domain\Resource\Entities\LogTriggers\JoinLogTrigger;

class ItemConsumer implements CostConsumerInterface
{
    private ?string $usrUserId = null;
    private ?string $costId = null;
    private ?int $costNum = null;

    public function __construct(
        private ItemDelegator $itemDelegator,
    ) {
    }

    /**
     * コストが足りているかチェックして消費情報に登録する
     *
     * @param string $usrUserId
     * @param ?string $costId
     * @param int $costNum
     * @param int $platform
     * @param string $billingPlatform
     * @param bool $checkedAd
     * @param ?GachaTrigger $gachaTrigger
     *
     * @return void
     */
    public function setConsumeResource(
        string $usrUserId,
        ?string $costId,
        int $costNum,
        int $platform,
        string $billingPlatform,
        bool $checkedAd,
        ?GachaTrigger $gachaTrigger
    ): void {
        $usrItem = $this->itemDelegator->getUsrItemByMstItemId($usrUserId, $costId);
        $usrItemAmount = is_null($usrItem) ? 0 : $usrItem->getAmount();
        if ($costNum > $usrItemAmount) {
            // 足りない場合
            throw new GameException(ErrorCode::ITEM_AMOUNT_IS_NOT_ENOUGH);
        }
        $this->usrUserId = $usrUserId;
        $this->costId = $costId;
        $this->costNum = $costNum;
    }

    /**
     * 消費情報で消費を行う
     *
     * @return void
     */
    public function execConsumeResource(ILogGachaAction $logGachaAction): void
    {
        $this->itemDelegator->useItemByMstItemId(
            $this->usrUserId,
            $this->costId,
            $this->costNum,
            new JoinLogTrigger($logGachaAction),
        );
    }
}
