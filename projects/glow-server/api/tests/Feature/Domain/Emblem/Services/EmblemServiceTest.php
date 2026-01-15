<?php

namespace Tests\Feature\Domain\Emblem\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Emblem\Constants\EmblemConstant;
use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Emblem\Services\EmblemService;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use Tests\Feature\Domain\Reward\Test1Reward;
use Tests\TestCase;

class EmblemServiceTest extends TestCase
{
    private EmblemService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(EmblemService::class);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_validateHasUsrEmblem_正常の場合()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $beforeMstEmblemId = '1';
        $mstEmblemId = '2';
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_emblem_id' => $beforeMstEmblemId,
        ]);
        MstEmblem::factory()->create([
            'id' => $mstEmblemId,
        ]);
        UsrEmblem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_emblem_id' => $mstEmblemId,
        ]);

        // Exercise
        $this->service->validateHasUsrEmblem($usrUserId, $mstEmblemId);
    }

    public function test_validateHasUsrEmblem_マスターデータにないエンブレムの場合()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $beforeMstEmblemId = '1';
        $mstEmblemId = '2';
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_emblem_id' => $beforeMstEmblemId,
        ]);
        UsrEmblem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_emblem_id' => $mstEmblemId,
        ]);

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);
        $this->service->validateHasUsrEmblem($usrUserId, $mstEmblemId);
    }

    public function test_validateHasUsrEmblem_ユーザが所持していないエンブレムの場合()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $beforeMstEmblemId = '1';
        $mstEmblemId = '2';
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_emblem_id' => $beforeMstEmblemId,
        ]);
        MstEmblem::factory()->create([
            'id' => $mstEmblemId,
        ]);

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::EMBLEM_NOT_OWNED);
        $this->service->validateHasUsrEmblem($usrUserId, $mstEmblemId);
    }

    public function test_registerInitialEmblems_エンブレムの初期配布処理()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $initialMstEmblemIds = collect(EmblemConstant::INITIAL_EMBLEM_MST_EMBLEM_IDS);

        // Exercise
        $this->service->registerInitialEmblems($usrUserId);
        $this->saveAll();

        // Verify
        $usrEmblems = UsrEmblem::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertCount($initialMstEmblemIds->count(), $usrEmblems);
        $usrEmblems->each(function (UsrEmblem $usrEmblem) use ($initialMstEmblemIds) {
            $this->assertTrue($initialMstEmblemIds->contains($usrEmblem->getMstEmblemId()));
        });
    }

    public function test_convertDuplicatedEmblemToCoin_重複獲得しているエンブレム報酬を別リソースへ変換できる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        // mst
        MstEmblem::factory()->createMany([
            ['id' => 'emblem1',],
            ['id' => 'emblem2',],
            ['id' => 'emblem3',],
            ['id' => 'emblem4',],
            ['id' => 'emblem5',],
        ]);
        MstItem::factory()->createMany([
            ['id' => 'item1'],
            ['id' => 'item2'],
        ]);

        // usr
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId, 'coin' => 1,]);
        UsrEmblem::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_emblem_id' => 'emblem3'],
            ['usr_user_id' => $usrUserId, 'mst_emblem_id' => 'emblem4'],
        ]);

        $rewards = collect([
            // emblem
            new Test1Reward(RewardType::EMBLEM, 'emblem1', 1),
            new Test1Reward(RewardType::EMBLEM, 'emblem2', 2),
            new Test1Reward(RewardType::EMBLEM, 'emblem3', 3),
            new Test1Reward(RewardType::EMBLEM, 'emblem4', 4),
            //   同じemblem5報酬を複数のRewardインスタンスに分けて設定
            new Test1Reward(RewardType::EMBLEM, 'emblem5', 1),
            new Test1Reward(RewardType::EMBLEM, 'emblem5', 1),
            new Test1Reward(RewardType::EMBLEM, 'emblem5', 3),
            // emblem以外の変換されないリソース
            new Test1Reward(RewardType::ITEM, 'item1', 1),
            new Test1Reward(RewardType::ITEM, 'item2', 2),
        ])->keyBy->getId();

        // Exercise
        $this->service->convertDuplicatedEmblemToCoin($usrUserId, $rewards);

        // Verify
        $this->assertCount(10, $rewards);

        $convertAmount = EmblemConstant::DUPLICATE_EMBLEM_CONVERT_COIN;
        $this->assertEqualsCanonicalizing([
            // 初獲得
            'Emblem-emblem1-1',
            'Emblem-emblem2-1',
            'Emblem-emblem5-1',
            // 重複獲得はコインへ変換されている
            'Coin--' . (2 - 1) * $convertAmount,
            'Coin--' . 3 * $convertAmount,
            'Coin--' . 4 * $convertAmount,
            'Coin--' . 1 * $convertAmount,
            'Coin--' . 3 * $convertAmount,
            // unit以外の変換されないリソースはそのまま
            'Item-item1-1',
            'Item-item2-2',
        ], $rewards->map(function (Test1Reward $reward) {
            return $reward->getType() . '-' . ($reward->getResourceId() ?? '') . '-' . $reward->getAmount();
        })->values()->toArray());
    }
}
