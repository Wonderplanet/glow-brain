<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\CurrencyTriggers;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use Illuminate\Support\Collection;

/**
 * まとめてダイヤを送信する際に指定するトリガー
 */
class FreeDiamondSendTrigger extends CurrencyBaseTrigger
{
    public const TYPE = 'free_diamond_send';

    /**
     * @param Collection<BaseReward> $rewards
     */
    public function __construct(
        Collection $rewards,
    ) {
        $details = [];
        $amounts = [];
        foreach ($rewards as $reward) {
            /** @var LogTriggerDto $logTriggerData */
            $logTriggerData = $reward->getLogTriggerData();

            $triggerKey = sprintf(
                '%s_%s_%s',
                $logTriggerData->getTriggerSource(),
                $logTriggerData->getTriggerValue(),
                $logTriggerData->getTriggerOption(),
            );
            $amounts[$triggerKey] = ($amounts[$triggerKey] ?? 0) + $reward->getAmount();
            $details[$triggerKey] = [
                'triggerSource' => $logTriggerData->getTriggerSource(),
                'triggerValue' => $logTriggerData->getTriggerValue(),
                'triggerOption' => $logTriggerData->getTriggerOption(),
                'amount' => $amounts[$triggerKey],
            ];
        }
        parent::__construct('', '', ['changeDetails' => array_values($details)]);
    }
}
