<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Reward\Services;

use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Encyclopedia\Models\UsrArtworkFragment;
use App\Domain\Encyclopedia\Services\EncyclopediaMissionTriggerService;
use App\Domain\Resource\Enums\RewardSendMethod;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstArtworkFragment;
use App\Domain\Reward\Entities\RewardSendContext;
use App\Domain\Reward\Services\ArtworkSendService;
use App\Domain\User\Constants\UserConstant;
use Illuminate\Support\Collection;
use Mockery;
use Tests\Feature\Domain\Reward\Test1Reward;
use Tests\TestCase;

class ArtworkSendServiceTest extends TestCase
{
    private ArtworkSendService $artworkSendService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artworkSendService = $this->app->make(ArtworkSendService::class);
    }

    public function test_send_新規Artworkと全Fragmentを正常に付与できる(): void
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        // マスタデータ作成
        $mstArtwork = MstArtwork::factory()->create([
            'id' => 'artwork_001',
        ]);

        $mstFragments = MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment_001', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_002', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_003', 'mst_artwork_id' => 'artwork_001'],
        ]);

        // 報酬作成
        $rewards = collect([
            new Test1Reward(RewardType::ARTWORK, 'artwork_001', 1, 'test_artwork_1'),
        ]);

        $context = new RewardSendContext(
            usrUserId: $usrUserId,
            platform: UserConstant::PLATFORM_IOS,
            rewards: $rewards,
            now: $now,
            sendMethod: RewardSendMethod::NONE,
        );

        // Exercise
        $result = $this->artworkSendService->send($context);
        $this->saveAll();

        // Verify
        // Artworkが作成されている
        $usrArtwork = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_id', 'artwork_001')
            ->first();
        $this->assertNotNull($usrArtwork, 'Artworkが作成されていません');

        // 全Fragmentが作成されている
        $usrFragments = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_id', 'artwork_001')
            ->get();
        $this->assertCount(3, $usrFragments, 'Fragmentが3つ作成されていません');

        // 報酬がsentとしてマークされている
        $this->assertTrue($rewards->first()->isSent());
    }

    public function test_send_既に所持しているArtworkは付与されない(): void
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        // マスタデータ作成
        $mstArtwork = MstArtwork::factory()->create([
            'id' => 'artwork_001',
        ]);

        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment_001', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_002', 'mst_artwork_id' => 'artwork_001'],
        ]);

        // 既にArtworkを所持している状態を作成
        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_artwork_id' => 'artwork_001',
        ]);

        UsrArtworkFragment::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_artwork_id' => 'artwork_001', 'mst_artwork_fragment_id' => 'fragment_001'],
            ['usr_user_id' => $usrUserId, 'mst_artwork_id' => 'artwork_001', 'mst_artwork_fragment_id' => 'fragment_002'],
        ]);

        $initialArtworkCount = UsrArtwork::query()->where('usr_user_id', $usrUserId)->count();
        $initialFragmentCount = UsrArtworkFragment::query()->where('usr_user_id', $usrUserId)->count();

        // 報酬作成
        $rewards = collect([
            new Test1Reward(RewardType::ARTWORK, 'artwork_001', 1, 'test_artwork_1'),
        ]);

        $context = new RewardSendContext(
            usrUserId: $usrUserId,
            platform: UserConstant::PLATFORM_IOS,
            rewards: $rewards,
            now: $now,
            sendMethod: RewardSendMethod::NONE,
        );

        // Exercise
        $result = $this->artworkSendService->send($context);
        $this->saveAll();

        // Verify - 数が増えていないことを確認
        $afterArtworkCount = UsrArtwork::query()->where('usr_user_id', $usrUserId)->count();
        $afterFragmentCount = UsrArtworkFragment::query()->where('usr_user_id', $usrUserId)->count();

        $this->assertEquals($initialArtworkCount, $afterArtworkCount, 'Artworkが重複して作成されています');
        $this->assertEquals($initialFragmentCount, $afterFragmentCount, 'Fragmentが重複して作成されています');

        // 報酬はsentとしてマークされている
        $this->assertTrue($rewards->first()->isSent());
    }

    public function test_send_複数のArtworkを同時に付与できる(): void
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        // マスタデータ作成
        MstArtwork::factory()->createMany([
            ['id' => 'artwork_001'],
            ['id' => 'artwork_002'],
        ]);

        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment_001', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_002', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_003', 'mst_artwork_id' => 'artwork_002'],
            ['id' => 'fragment_004', 'mst_artwork_id' => 'artwork_002'],
        ]);

        // 報酬作成
        $rewards = collect([
            new Test1Reward(RewardType::ARTWORK, 'artwork_001', 1, 'test_artwork_1'),
            new Test1Reward(RewardType::ARTWORK, 'artwork_002', 1, 'test_artwork_2'),
        ]);

        $context = new RewardSendContext(
            usrUserId: $usrUserId,
            platform: UserConstant::PLATFORM_IOS,
            rewards: $rewards,
            now: $now,
            sendMethod: RewardSendMethod::NONE,
        );

        // Exercise
        $result = $this->artworkSendService->send($context);
        $this->saveAll();

        // Verify
        // 2つのArtworkが作成されている
        $usrArtworks = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->get();
        $this->assertCount(2, $usrArtworks, 'Artworkが2つ作成されていません');

        // 全Fragmentが作成されている
        $usrFragments = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->get();
        $this->assertCount(4, $usrFragments, 'Fragmentが4つ作成されていません');

        // 全報酬がsentとしてマークされている
        $this->assertTrue($rewards->every(fn($reward) => $reward->isSent()));
    }

    public function test_send_一部Fragmentを所持している場合でも正しく処理される(): void
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        // マスタデータ作成
        MstArtwork::factory()->create([
            'id' => 'artwork_001',
        ]);

        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment_001', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_002', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_003', 'mst_artwork_id' => 'artwork_001'],
        ]);

        // 一部のFragmentを既に所持している状態を作成
        UsrArtworkFragment::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_artwork_id' => 'artwork_001',
            'mst_artwork_fragment_id' => 'fragment_001',
        ]);

        // 報酬作成
        $rewards = collect([
            new Test1Reward(RewardType::ARTWORK, 'artwork_001', 1, 'test_artwork_1'),
        ]);

        $context = new RewardSendContext(
            usrUserId: $usrUserId,
            platform: UserConstant::PLATFORM_IOS,
            rewards: $rewards,
            now: $now,
            sendMethod: RewardSendMethod::NONE,
        );

        // Exercise
        $result = $this->artworkSendService->send($context);
        $this->saveAll();

        // Verify
        // Artworkが作成されている
        $usrArtwork = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_id', 'artwork_001')
            ->first();
        $this->assertNotNull($usrArtwork, 'Artworkが作成されていません');

        // 全Fragmentが作成されている（既存の1つ + 新規の2つ = 3つ）
        $usrFragments = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_id', 'artwork_001')
            ->get();
        $this->assertCount(3, $usrFragments, 'Fragmentが3つになっていません');

        // fragment_001が重複していないことを確認
        $fragmentIds = $usrFragments->pluck('mst_artwork_fragment_id')->toArray();
        $uniqueFragmentIds = array_unique($fragmentIds);
        $this->assertCount(3, $uniqueFragmentIds, 'Fragmentが重複しています');
    }
}
