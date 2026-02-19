<?php

namespace Tests\Feature\Log;

use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Resource\Mst\Models\MstFragmentBox;
use App\Domain\Resource\Mst\Models\MstFragmentBoxGroup;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;
use Tests\Feature\Http\Controllers\BaseControllerTestCase;
use Tests\Support\Traits\TestLogTrait;

class LogItemTest extends BaseControllerTestCase
{
    use TestLogTrait;

    protected string $baseUrl = '/api/';

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_item_consume_ランダムかけらBOXとの交換でのアイテム消費ログが保存されている()
    {
        // Setup
        $now = $this->fixTime('2023-01-01 00:00:00');
        $nginxRequestId = __FUNCTION__;
        $this->setNginxRequestId($nginxRequestId);

        $usrUserId = $this->createUsrUser()->getId();

        MstItem::factory()->createMany([
            ['id' => 'use_item_1', 'type' => ItemType::RANDOM_FRAGMENT_BOX],
            ['id' => 'get_candidate_item_1'],
            ['id' => 'get_candidate_item_2'],
        ]);
        MstFragmentBox::factory()->create([
            'id' => 'fragment_box_1',
            'mst_item_id' => 'use_item_1',
            'mst_fragment_box_group_id' => 'fragment_box_group_1',
        ]);
        MstFragmentBoxGroup::factory()->createMany([
            [
                'mst_fragment_box_group_id' => 'fragment_box_group_1',
                'mst_item_id' => 'get_candidate_item_1',
            ],
            [
                'mst_fragment_box_group_id' => 'fragment_box_group_1',
                'mst_item_id' => 'get_candidate_item_2',
            ],
        ]);
        UsrItem::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'use_item_1', 'amount' => 10],
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 100,
        ]);
        $this->createDiamond($usrUserId);
        MstUserLevel::factory()->createMany([
            ['level' => 1]
        ]);

        // Exercise
        $requestData = [
            'mstItemId' => 'use_item_1',
            'amount' => 2,
        ];
        $response = $this->sendRequest('item/consume', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // 0,1はアイテム獲得ログで、2はアイテム消費ログ
        $this->checkLoggingNo($usrUserId, 3);

        $this->checkLogResourcesByUse(
            usrUserId: $usrUserId,
            nginxRequestId: $nginxRequestId,
            rewardType: RewardType::ITEM,
            expectedAmounts: [
                'use_item_1' => [['before_amount' => 10, 'after_amount' => 8]],
            ],
            expectedTriggers: [
                [
                    'trigger_source' => LogResourceTriggerSource::ITEM_FRAGMENT_BOX_COST->value,
                    'trigger_value' => ItemType::RANDOM_FRAGMENT_BOX->value,
                    'trigger_option' => '',
                ],
            ],
        );
    }
}
