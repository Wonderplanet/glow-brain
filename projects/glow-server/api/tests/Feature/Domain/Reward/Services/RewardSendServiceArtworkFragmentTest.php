<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Reward\Services;

use App\Domain\Encyclopedia\Models\LogArtworkFragment;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Encyclopedia\Models\UsrArtworkFragment;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstArtworkFragment;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Reward\Managers\RewardManager;
use App\Domain\Reward\Services\RewardSendService;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use Tests\Feature\Domain\Reward\Test1Reward;
use Tests\TestCase;

/**
 * RewardSendService - 原画のかけら報酬配布テスト
 *
 * このテストクラスは原画のかけら（ARTWORK_FRAGMENT）報酬配布の詳細なテストを行います。
 *
 * テスト対象：
 * - LogArtworkFragmentRepository連携（ログ記録）
 * - convertRewards内のARTWORK_FRAGMENT処理（原画完成チェック）
 * - addArtworkRewardWhenArtworkCompleted呼び出し
 * - メールボックス送信（SendMethod.MESSAGE対応）
 */
class RewardSendServiceArtworkFragmentTest extends TestCase
{
    private RewardSendService $rewardSendService;
    private RewardManager $rewardManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rewardSendService = $this->app->make(RewardSendService::class);
        $this->rewardManager = $this->app->make(RewardManager::class);
    }

    /**
     * テスト用のマスターデータを作成する
     */
    private function createMasterData(): void
    {
        // テスト用原画（3つのかけらで完成する原画）
        MstArtwork::factory()->createMany([
            ['id' => 'artwork_001'],
            ['id' => 'artwork_002'],
        ]);

        // テスト用原画のかけら
        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment_001_1', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_001_2', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_001_3', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_002_1', 'mst_artwork_id' => 'artwork_002'],
            ['id' => 'fragment_002_2', 'mst_artwork_id' => 'artwork_002'],
            ['id' => 'fragment_002_3', 'mst_artwork_id' => 'artwork_002'],
        ]);
    }

    /**
     * 原画のかけら配布でログが正しく記録されることを確認するテスト
     */
    public function test_sendRewards_原画のかけら配布でログが正しく記録される(): void
    {
        // Setup
        $platform = UserConstant::PLATFORM_IOS;
        $now = $this->fixTime();

        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
            'stamina' => 100,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        $this->createDiamond(usrUserId: $usrUserId, freeDiamond: 0);
        $this->createMasterData();

        // 2つの異なる原画のかけらを配布（どちらも原画未完成）
        $rewards = collect([
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_001_1', 1, 'test_fragment_1'),
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_002_1', 1, 'test_fragment_2'),
        ]);

        $this->rewardManager->addRewards($rewards);

        // Exercise
        $this->rewardSendService->sendRewards(
            usrUserId: $usrUserId,
            platform: $platform,
            now: $now,
            policy: null
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify - かけらが配布されている
        $usrFragments = UsrArtworkFragment::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertEquals(2, $usrFragments->count(), 'かけら数が期待値と一致しません');

        // Verify - ログが正しく記録されている（かけら獲得ログ）
        $logs = LogArtworkFragment::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertEquals(2, $logs->count(), 'ログ数が期待値と一致しません');

        // ログの内容を確認
        // contentTypeはLogTriggerDtoのtriggerSource（= 'Test1Reward'）が格納される
        // targetIdにはtriggerValue（= テストID）が格納される
        $log1 = $logs->firstWhere('mst_artwork_fragment_id', 'fragment_001_1');
        $this->assertNotNull($log1, 'fragment_001_1のログが存在しません');
        $this->assertEquals('Test1Reward', $log1->content_type, 'contentTypeが一致しません');
        $this->assertEquals('test_fragment_1', $log1->target_id, 'targetIdが一致しません');
        $this->assertFalse((bool)$log1->is_complete_artwork, '原画未完成フラグが正しくありません');

        $log2 = $logs->firstWhere('mst_artwork_fragment_id', 'fragment_002_1');
        $this->assertNotNull($log2, 'fragment_002_1のログが存在しません');
        $this->assertEquals('Test1Reward', $log2->content_type, 'contentTypeが一致しません');
        $this->assertEquals('test_fragment_2', $log2->target_id, 'targetIdが一致しません');
        $this->assertFalse((bool)$log2->is_complete_artwork, '原画未完成フラグが正しくありません');
    }

    /**
     * 報酬リストに原画報酬とかけら報酬の両方が入っていて、かけらで原画が完成し、
     * 元々の原画報酬が重複原画となりコインに変換されることを確認するテスト
     */
    public function test_sendRewards_かけらで原画完成時に報酬リスト内の原画が重複原画としてコインに変換される(): void
    {
        // Setup
        $platform = UserConstant::PLATFORM_IOS;
        $now = $this->fixTime();

        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
            'stamina' => 100,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        $this->createDiamond(usrUserId: $usrUserId, freeDiamond: 0);
        $this->createMasterData();

        // 既に2つのかけらを所持（あと1つで完成）
        UsrArtworkFragment::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => 'artwork_001',
                'mst_artwork_fragment_id' => 'fragment_001_1',
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => 'artwork_001',
                'mst_artwork_fragment_id' => 'fragment_001_2',
            ],
        ]);

        // 報酬リストに、最後のかけらと原画本体の両方を追加
        // かけらで原画が完成するため、原画本体は重複原画となりコインに変換されるべき
        $rewards = collect([
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_001_3', 1, 'test_fragment_complete'),
            new Test1Reward(RewardType::ARTWORK, 'artwork_001', 1, 'test_artwork_duplicate'),
        ]);

        $this->rewardManager->addRewards($rewards);

        // 初期コインを確認
        $usrUserParameterBefore = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(0, $usrUserParameterBefore->getCoin(), '初期コインは0であるべき');

        // Exercise
        $this->rewardSendService->sendRewards(
            usrUserId: $usrUserId,
            platform: $platform,
            now: $now,
            policy: null
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify - 3つ目のかけらが配布されている
        $usrFragment3 = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_fragment_id', 'fragment_001_3')
            ->first();
        $this->assertNotNull($usrFragment3, '3つ目のかけらが配布されていません');

        // Verify - 原画が配布されている（かけらで完成）
        $usrArtwork = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_id', 'artwork_001')
            ->first();
        $this->assertNotNull($usrArtwork, '原画が配布されていません');

        // Verify - 重複原画がコインに変換されている
        // EncyclopediaConstant::DUPLICATE_ARTWORK_CONVERT_COIN = 30000
        $usrUserParameterAfter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(30000, $usrUserParameterAfter->getCoin(), '重複原画が30000コインに変換されているべき');

        // Verify - かけら配布ログが正しく記録されている
        $log = LogArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_fragment_id', 'fragment_001_3')
            ->first();
        $this->assertNotNull($log, 'かけら配布ログが存在しません');
        $this->assertTrue((bool)$log->is_complete_artwork, '原画完成フラグがtrueになっていません');
        $this->assertEquals('Test1Reward', $log->content_type, 'contentTypeが一致しません');
        $this->assertEquals('test_fragment_complete', $log->target_id, 'targetIdが一致しません');
    }

    /**
     * 原画のかけら配布で原画完成時にArtworkFragmentCompletionRewardが追加されることを確認するテスト
     */
    public function test_sendRewards_原画のかけら配布で原画完成時にArtworkFragmentCompletionRewardが追加される(): void
    {
        // Setup
        $platform = UserConstant::PLATFORM_IOS;
        $now = $this->fixTime();

        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
            'stamina' => 100,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        $this->createDiamond(usrUserId: $usrUserId, freeDiamond: 0);
        $this->createMasterData();

        // 既に2つのかけらを所持
        UsrArtworkFragment::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => 'artwork_001',
                'mst_artwork_fragment_id' => 'fragment_001_1',
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => 'artwork_001',
                'mst_artwork_fragment_id' => 'fragment_001_2',
            ],
        ]);

        // 最後のかけらを配布（原画が完成する）
        $rewards = collect([
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_001_3', 1, 'test_fragment_complete'),
        ]);

        $this->rewardManager->addRewards($rewards);

        // Exercise
        $this->rewardSendService->sendRewards(
            usrUserId: $usrUserId,
            platform: $platform,
            now: $now,
            policy: null
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify - 3つ目のかけらが配布されている
        $usrFragment3 = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_fragment_id', 'fragment_001_3')
            ->first();
        $this->assertNotNull($usrFragment3, '3つ目のかけらが配布されていません');

        // Verify - 原画が自動的に配布されている
        $usrArtwork = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_id', 'artwork_001')
            ->first();
        $this->assertNotNull($usrArtwork, '原画が自動配布されていません');

        // Verify - ログが正しく記録されている（原画完成フラグがtrueになっている）
        $log = LogArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_fragment_id', 'fragment_001_3')
            ->first();
        $this->assertNotNull($log, 'かけら配布ログが存在しません');
        $this->assertTrue((bool)$log->is_complete_artwork, '原画完成フラグがtrueになっていません');
        $this->assertEquals('Test1Reward', $log->content_type, 'contentTypeが一致しません');
        $this->assertEquals('test_fragment_complete', $log->target_id, 'targetIdが一致しません');
    }

    /**
     * 複数原画のかけらを一括配布して複数原画が完成することを確認するテスト
     */
    public function test_sendRewards_複数原画のかけらを一括配布して複数原画が完成する(): void
    {
        // Setup
        $platform = UserConstant::PLATFORM_IOS;
        $now = $this->fixTime();

        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
            'stamina' => 100,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        $this->createDiamond(usrUserId: $usrUserId, freeDiamond: 0);
        $this->createMasterData();

        // artwork_001: 既に2つのかけらを所持（あと1つで完成）
        UsrArtworkFragment::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => 'artwork_001',
                'mst_artwork_fragment_id' => 'fragment_001_1',
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => 'artwork_001',
                'mst_artwork_fragment_id' => 'fragment_001_2',
            ],
        ]);

        // artwork_002: 既に1つのかけらを所持（あと2つで完成）
        UsrArtworkFragment::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_artwork_id' => 'artwork_002',
            'mst_artwork_fragment_id' => 'fragment_002_1',
        ]);

        // 両方の原画を完成させるかけらを一括配布
        $rewards = collect([
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_001_3', 1, 'test_artwork_001_complete'),
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_002_2', 1, 'test_artwork_002_progress'),
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_002_3', 1, 'test_artwork_002_complete'),
        ]);

        $this->rewardManager->addRewards($rewards);

        // Exercise
        $this->rewardSendService->sendRewards(
            usrUserId: $usrUserId,
            platform: $platform,
            now: $now,
            policy: null
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify - 両方の原画が自動的に配布されている
        $usrArtworks = UsrArtwork::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertEquals(2, $usrArtworks->count(), '2つの原画が配布されているべき');

        $mstArtworkIds = $usrArtworks->pluck('mst_artwork_id')->toArray();
        $this->assertContains('artwork_001', $mstArtworkIds, 'artwork_001が配布されていません');
        $this->assertContains('artwork_002', $mstArtworkIds, 'artwork_002が配布されていません');

        // Verify - ログが正しく記録されている
        $logs = LogArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->orderBy('id')
            ->get();
        $this->assertEquals(3, $logs->count(), 'ログ数が期待値と一致しません');

        // artwork_001完成ログ確認
        $log001 = $logs->firstWhere('mst_artwork_fragment_id', 'fragment_001_3');
        $this->assertNotNull($log001, 'fragment_001_3のログが存在しません');
        $this->assertTrue((bool)$log001->is_complete_artwork, 'artwork_001完成フラグがtrueになっていません');

        // artwork_002かけらログ確認
        // 注意: 原画が完成する場合、その原画に紐づく全てのかけら報酬にis_complete_artwork=trueが設定される
        // これは、addArtworkRewardWhenArtworkCompletedで、完成する原画に関連する全てのかけら報酬に
        // triggerOptionをセットする仕様のため
        $log002_2 = $logs->firstWhere('mst_artwork_fragment_id', 'fragment_002_2');
        $this->assertNotNull($log002_2, 'fragment_002_2のログが存在しません');
        $this->assertTrue((bool)$log002_2->is_complete_artwork, 'artwork_002完成フラグがtrueになっていません');

        $log002_3 = $logs->firstWhere('mst_artwork_fragment_id', 'fragment_002_3');
        $this->assertNotNull($log002_3, 'fragment_002_3のログが存在しません');
        $this->assertTrue((bool)$log002_3->is_complete_artwork, 'artwork_002完成フラグがtrueになっていません');
    }

    /**
     * 原画のかけらと原画の両方が報酬にある場合の動作確認テスト
     */
    public function test_sendRewards_原画のかけらと原画の両方が報酬にある場合の動作確認(): void
    {
        // Setup
        $platform = UserConstant::PLATFORM_IOS;
        $now = $this->fixTime();

        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
            'stamina' => 100,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        $this->createDiamond(usrUserId: $usrUserId, freeDiamond: 0);

        // マスタデータ作成
        MstArtwork::factory()->createMany([
            ['id' => 'artwork_001'],
            ['id' => 'artwork_002'],
        ]);
        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment_001', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_002', 'mst_artwork_id' => 'artwork_002'],
        ]);

        // 報酬作成（かけらと原画の両方）
        $rewards = collect([
            new Test1Reward(RewardType::ARTWORK_FRAGMENT, 'fragment_001', 1, 'test_fragment_1'),
            new Test1Reward(RewardType::ARTWORK, 'artwork_002', 1, 'test_artwork_2'),
        ]);

        $this->rewardManager->addRewards($rewards);

        // Exercise
        $result = $this->rewardSendService->sendRewards(
            usrUserId: $usrUserId,
            platform: $platform,
            now: $now,
            policy: null
        );
        $this->saveAll();

        // Verify - かけらが配布されている
        $usrFragment = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_fragment_id', 'fragment_001')
            ->first();
        $this->assertNotNull($usrFragment, 'かけらが配布されていません');

        // Verify - 原画が配布されている
        $usrArtwork = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_id', 'artwork_002')
            ->first();
        $this->assertNotNull($usrArtwork, '原画が配布されていません');
    }

}
