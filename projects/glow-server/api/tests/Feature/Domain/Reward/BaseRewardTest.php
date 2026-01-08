<?php

namespace Tests\Feature\Domain\Common\Entities;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Dtos\RewardDto;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Enums\RewardConvertedReason;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Enums\UnreceivedRewardReason;
use Tests\TestCase;

class BaseRewardTest extends TestCase
{
    public function test_formatToResponse_配列データの確認(): void
    {
        // Setup

        // 変換なし、resource_idなし
        $reward1 = new BaseReward(
            type: RewardType::COIN->value,
            resourceId: null,
            amount: 100,
            logTriggerData: new LogTriggerDto('test_source1', 'test_value1', 'test_option1'),
        );
        // 変換なし、resource_idあり
        $reward2 = new BaseReward(
            type: RewardType::ITEM->value,
            resourceId: 'test_item_1',
            amount: 5,
            logTriggerData: new LogTriggerDto('test_source2', 'test_value2', 'test_option2'),
        );

        // 変換あり
        $reward3 = new BaseReward(
            type: RewardType::EMBLEM->value,
            resourceId: 'test_emblem_1',
            amount: 1,
            logTriggerData: new LogTriggerDto('test_source3', 'test_value3', 'test_option3'),
        );
        $reward3->setRewardData(
            new RewardDto(
                type: RewardType::COIN->value,
                resourceId: null,
                amount: 100,
            )
        );
        $reward3->setRewardConvertedReason(RewardConvertedReason::DUPLICATED_EMBLEM);

        // 変換なし、未配布でメールボックスに送信した
        $reward4 = new BaseReward(
            type: RewardType::COIN->value,
            resourceId: null,
            amount: 100,
            logTriggerData: new LogTriggerDto('test_source4', 'test_value4', 'test_option4'),
        );
        $reward4->setUnreceivedRewardReason(UnreceivedRewardReason::SENT_TO_MESSAGE);

        // Exercise

        // Verify
        $this->assertEqualsCanonicalizing(
            [
                'unreceivedRewardReasonType' => UnreceivedRewardReason::NONE->value,
                'resourceType' => RewardType::COIN->value,
                'resourceId' => null,
                'resourceAmount' => 100,
                'preConversionResource' => null,
            ],
            $reward1->formatToResponse()
        );

        $this->assertEqualsCanonicalizing(
            [
                'unreceivedRewardReasonType' => UnreceivedRewardReason::NONE->value,
                'resourceType' => RewardType::ITEM->value,
                'resourceId' => 'test_item_1',
                'resourceAmount' => 5,
                'preConversionResource' => null,
            ],
            $reward2->formatToResponse()
        );

        $this->assertEqualsCanonicalizing(
            [
                'unreceivedRewardReasonType' => UnreceivedRewardReason::NONE->value,
                'resourceType' => RewardType::COIN->value,
                'resourceId' => null,
                'resourceAmount' => 100,
                'preConversionResource' => [
                    'resourceType' => RewardType::EMBLEM->value,
                    'resourceId' => 'test_emblem_1',
                    'resourceAmount' => 1,
                ],
            ],
            $reward3->formatToResponse()
        );

        $this->assertEqualsCanonicalizing(
            [
                'unreceivedRewardReasonType' => UnreceivedRewardReason::SENT_TO_MESSAGE->value,
                'resourceType' => RewardType::COIN->value,
                'resourceId' => null,
                'resourceAmount' => 100,
                'preConversionResource' => null,
            ],
            $reward4->formatToResponse()
        );
    }
}
