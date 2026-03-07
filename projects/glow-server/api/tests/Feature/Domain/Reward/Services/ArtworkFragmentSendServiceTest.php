<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Reward\Services;

use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Encyclopedia\Models\UsrArtworkFragment;
use App\Domain\Resource\Enums\RewardSendMethod;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstArtworkFragment;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Reward\Entities\RewardSendContext;
use App\Domain\Reward\Services\ArtworkFragmentSendService;
use App\Domain\User\Constants\UserConstant;
use Tests\Feature\Domain\Reward\Test1Reward;
use Tests\TestCase;

class ArtworkFragmentSendServiceTest extends TestCase
{
    private ArtworkFragmentSendService $artworkFragmentSendService;
    private RewardDelegator $rewardDelegator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artworkFragmentSendService = $this->app->make(ArtworkFragmentSendService::class);
        $this->rewardDelegator = $this->app->make(RewardDelegator::class);
    }

    public function test_send_原画のかけらを正常に付与できる(): void
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        // マスタデータ作成
        $mstFragment = MstArtworkFragment::factory()->create([
            'id' => 'fragment_001',
            'mst_artwork_id' => 'artwork_001',
        ]);

        // 報酬作成
        $rewards = collect([
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_001', 1, 'test_fragment_1'),
        ]);

        $context = new RewardSendContext(
            usrUserId: $usrUserId,
            platform: UserConstant::PLATFORM_IOS,
            rewards: $rewards,
            now: $now,
            sendMethod: RewardSendMethod::NONE,
        );

        // Exercise
        $result = $this->artworkFragmentSendService->send($context);
        $this->saveAll();

        // Verify
        // かけらが作成されている
        $usrFragment = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_fragment_id', 'fragment_001')
            ->first();
        $this->assertNotNull($usrFragment, 'かけらが作成されていません');

        // 報酬がsent状態になっている
        $this->assertTrue($rewards->first()->isSent(), '報酬がsent状態になっていません');
    }

    /**
     * 全かけら揃った時にかけらが配布されることを確認するテスト
     *
     * 注意: ArtworkFragmentSendServiceは原画の自動完成判定を行わない。
     * 原画の完成判定はRewardSendService::beforeSend内のconvertRewardsで行われる。
     * 原画完成のテストはRewardSendServiceArtworkFragmentTestを参照。
     */
    public function test_send_全かけら揃った時にかけらが配布される(): void
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        // マスタデータ作成（3つのかけら）
        $mstFragments = MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment_001', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_002', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_003', 'mst_artwork_id' => 'artwork_001'],
        ]);

        // 既に2つのかけらを所持
        UsrArtworkFragment::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => 'artwork_001',
                'mst_artwork_fragment_id' => 'fragment_001',
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => 'artwork_001',
                'mst_artwork_fragment_id' => 'fragment_002',
            ],
        ]);

        // 最後のかけらを報酬として配布
        $rewards = collect([
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_003', 1, 'test_fragment_3'),
        ]);

        $context = new RewardSendContext(
            usrUserId: $usrUserId,
            platform: UserConstant::PLATFORM_IOS,
            rewards: $rewards,
            now: $now,
            sendMethod: RewardSendMethod::NONE,
        );

        // Exercise
        $result = $this->artworkFragmentSendService->send($context);
        $this->saveAll();

        // Verify
        // 3つ目のかけらが作成されている
        $usrFragment3 = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_fragment_id', 'fragment_003')
            ->first();
        $this->assertNotNull($usrFragment3, '3つ目のかけらが作成されていません');

        // 報酬がsent状態になっている
        $this->assertTrue($rewards->first()->isSent(), '報酬がsent状態になっていません');

        // 注意: ArtworkFragmentSendServiceでは原画は自動作成されない
        // 原画の完成判定はRewardSendService経由でのみ行われる
    }

    /**
     * 既に所持しているかけらは重複作成しないことを確認するテスト
     */
    public function test_send_既に所持しているかけらは重複作成しない(): void
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        // マスタデータ作成
        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment_001', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_002', 'mst_artwork_id' => 'artwork_001'],
        ]);

        // 既にfragment_001を所持
        UsrArtworkFragment::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_artwork_id' => 'artwork_001',
            'mst_artwork_fragment_id' => 'fragment_001',
        ]);

        // 既存かけらと新規かけらを配布
        $rewards = collect([
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_001', 1, 'test_existing'),
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_002', 1, 'test_new'),
        ]);

        $context = new RewardSendContext(
            usrUserId: $usrUserId,
            platform: UserConstant::PLATFORM_IOS,
            rewards: $rewards,
            now: $now,
            sendMethod: RewardSendMethod::NONE,
        );

        // Exercise
        $result = $this->artworkFragmentSendService->send($context);
        $this->saveAll();

        // Verify - fragment_001は1つのみ（重複作成されていない）
        $fragment001Count = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_fragment_id', 'fragment_001')
            ->count();
        $this->assertEquals(1, $fragment001Count, 'fragment_001は1つのみ存在するべき（重複作成されていない）');

        // Verify - fragment_002は新規作成されている
        $fragment002 = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_fragment_id', 'fragment_002')
            ->first();
        $this->assertNotNull($fragment002, 'fragment_002は新規作成されているべき');

        // Verify - 全体で2つのかけらが存在
        $totalFragmentCount = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->count();
        $this->assertEquals(2, $totalFragmentCount, '全体で2つのかけらが存在するべき');
    }

    /**
     * 異なる原画のかけらを同時に配布できることを確認するテスト
     */
    public function test_send_異なる原画のかけらを同時に配布できる(): void
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        // マスタデータ作成（2つの異なる原画）
        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment_001_1', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_001_2', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_002_1', 'mst_artwork_id' => 'artwork_002'],
            ['id' => 'fragment_002_2', 'mst_artwork_id' => 'artwork_002'],
        ]);

        // 異なる原画のかけらを同時に配布
        $rewards = collect([
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_001_1', 1, 'test_artwork1_1'),
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_001_2', 1, 'test_artwork1_2'),
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_002_1', 1, 'test_artwork2_1'),
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_002_2', 1, 'test_artwork2_2'),
        ]);

        $context = new RewardSendContext(
            usrUserId: $usrUserId,
            platform: UserConstant::PLATFORM_IOS,
            rewards: $rewards,
            now: $now,
            sendMethod: RewardSendMethod::NONE,
        );

        // Exercise
        $result = $this->artworkFragmentSendService->send($context);
        $this->saveAll();

        // Verify - 4つのかけらが作成されている
        $usrFragments = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->get();
        $this->assertEquals(4, $usrFragments->count(), '4つのかけらが作成されているべき');

        // Verify - artwork_001のかけらが2つ
        $artwork001FragmentCount = $usrFragments->where('mst_artwork_id', 'artwork_001')->count();
        $this->assertEquals(2, $artwork001FragmentCount, 'artwork_001のかけらが2つあるべき');

        // Verify - artwork_002のかけらが2つ
        $artwork002FragmentCount = $usrFragments->where('mst_artwork_id', 'artwork_002')->count();
        $this->assertEquals(2, $artwork002FragmentCount, 'artwork_002のかけらが2つあるべき');

        // Verify - 全ての報酬がsent状態
        $this->assertTrue($rewards->every(fn($reward) => $reward->isSent()), '全ての報酬がsent状態になっているべき');
    }
}
