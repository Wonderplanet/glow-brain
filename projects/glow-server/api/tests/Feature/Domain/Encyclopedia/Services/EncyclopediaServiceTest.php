<?php

namespace Feature\Domain\Encyclopedia\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Encyclopedia\Models\LogArtworkFragment;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Encyclopedia\Models\UsrArtworkFragment;
use App\Domain\Encyclopedia\Models\UsrReceivedUnitEncyclopediaReward;
use App\Domain\Encyclopedia\Services\EncyclopediaService;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstArtworkFragment;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaReward;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\MstUserLevelBonus;
use App\Domain\Resource\Mst\Models\MstUserLevelBonusGroup;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Tests\Support\Traits\TestRewardTrait;
use Tests\TestCase;

use App\Domain\Unit\Models\UsrUnitSummary;

class EncyclopediaServiceTest extends TestCase
{
    use TestRewardTrait;

    const TARGET_SINGLE_GRADE_LEVEL = 5;
    private EncyclopediaService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(EncyclopediaService::class);
    }

    public static function params_validateUnitEncyclopediaRank_図鑑ランク検証(): array
    {
        return [
            '図鑑ランクをユニット1体で達成している' => [
                'gradeLevels' => collect([self::TARGET_SINGLE_GRADE_LEVEL]),
                'unitEncyclopediaRanks' => collect([5]),
                'isExceptionThrown' => false,
            ],
            '図鑑ランクをユニット複数体で達成する' => [
                'gradeLevels' => collect([2, 3]),
                'unitEncyclopediaRanks' => collect([5]),
                'isExceptionThrown' => false,
            ],
            'すべて図鑑ランクに到達している' => [
                'gradeLevels' => collect([self::TARGET_SINGLE_GRADE_LEVEL]),
                'unitEncyclopediaRanks' => collect([1, 5]),
                'isExceptionThrown' => false,
            ],
            '図鑑ランクに到達していないものを含む' => [
                'gradeLevels' => collect([self::TARGET_SINGLE_GRADE_LEVEL]),
                'unitEncyclopediaRanks' => collect([1, 5, 6]),
                'isExceptionThrown' => true,
            ],
        ];
    }

    /**
     * @dataProvider params_validateUnitEncyclopediaRank_図鑑ランク検証
     */
    public function testValidateUnitEncyclopediaRank_図鑑ランク検証(
        Collection $gradeLevels,
        Collection $unitEncyclopediaRanks,
        bool $isExceptionThrown
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $mstUnitEncyclopediaRewardIds = $unitEncyclopediaRanks->map(function (int $rank) {
            return MstUnitEncyclopediaReward::factory()->create([
                'unit_encyclopedia_rank' => $rank,
            ])->toEntity()->getId();
        });
        $gradeLevels->each(function (int $gradeLevel) use ($usrUserId) {
            UsrUnit::factory()->create([
                'usr_user_id' => $usrUserId,
                'grade_level' => $gradeLevel,
            ]);
        });

        // ユーザの図鑑ランクを取得するためにUsrUnitSummaryを作成
        UsrUnitSummary::factory()->create([
            'usr_user_id' => $usrUserId,
            'grade_level_total_count' => self::TARGET_SINGLE_GRADE_LEVEL,
        ]);

        // Exercise
        if ($isExceptionThrown) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::ENCYCLOPEDIA_NOT_REACHED_ENCYCLOPEDIA_RANK);
        }
        $this->execPrivateMethod(
            $this->service,
            'validateUnitEncyclopediaRank',
            [$usrUserId, $mstUnitEncyclopediaRewardIds]
        );

        // エラーが起きないテストはassertがないのでダミーでassertを入れる
        $this->assertTrue(true);
    }

    public static function params_validateReceived_受け取り可能検証(): array
    {
        return [
            'すべて受け取り可能' => [
                'mstUnitEncyclopediaRewardIds' => collect(['reward1', 'reward2', 'reward3']),
                'receivedRewardIds' => collect(),
                'isExceptionThrown' => false,
            ],
            '受け取り済みを含む' => [
                'mstUnitEncyclopediaRewardIds' => collect(['reward1', 'reward2', 'reward3']),
                'receivedRewardIds' => collect(['reward2']),
                'isExceptionThrown' => true,
            ],
            'IDの重複がある' => [
                'mstUnitEncyclopediaRewardIds' => collect(['reward1', 'reward2', 'reward1']),
                'receivedRewardIds' => collect(['reward2']),
                'isExceptionThrown' => true,
            ],
        ];
    }

    /**
     * @dataProvider params_validateReceived_受け取り可能検証
     */
    public function testValidateReceived_受け取り可能検証(
        Collection $mstUnitEncyclopediaRewardIds,
        Collection $receivedRewardIds,
        bool $isExceptionThrown
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $receivedRewardIds->each(function (string $rewardId) use ($usrUserId) {
            UsrReceivedUnitEncyclopediaReward::factory()->create([
                'usr_user_id' => $usrUserId,
                'mst_unit_encyclopedia_reward_id' => $rewardId,
            ]);
        });

        // Exercise
        if ($isExceptionThrown) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::ENCYCLOPEDIA_REWARD_RECEIVED);
        }
        $this->execPrivateMethod($this->service, 'validateReceived', [$usrUserId, $mstUnitEncyclopediaRewardIds]);

        // エラーが起きないテストはassertがないのでダミーでassertを入れる
        $this->assertTrue(true);
    }

    public function testReceiveReward_報酬受け取り()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $mstItem = MstItem::factory()->create()->toEntity();
        $mstEmblem = MstEmblem::factory()->create()->toEntity();
        // 配布可能な全報酬
        $mstUnitEncyclopediaRewards = MstUnitEncyclopediaReward::factory()->createMany([
            [
                'unit_encyclopedia_rank' => 1,
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 10,
            ],
            [
                'unit_encyclopedia_rank' => 2,
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 20,
            ],
            [
                'unit_encyclopedia_rank' => 3,
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => $mstItem->getId(),
                'resource_amount' => 30,
            ],
            [
                'unit_encyclopedia_rank' => 4,
                'resource_type' => RewardType::EMBLEM->value,
                'resource_id' => $mstEmblem->getId(),
                'resource_amount' => 1,
            ],
            [
                'unit_encyclopedia_rank' => 5,
                'resource_type' => RewardType::EXP->value,
                'resource_id' => NULL,
                'resource_amount' => 40,
            ],
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'exp' => 0],
            ['level' => 2, 'exp' => 140],
        ]);
        $mstUserLevelBonus = MstUserLevelBonus::factory()->create(['level' => 2])->toEntity();
        MstUserLevelBonusGroup::factory()->create([
            'mst_user_level_bonus_group_id' => $mstUserLevelBonus->getMstUserLevelBonusGroupId(),
            'resource_type' => RewardType::COIN->value,
            'resource_id' => NULL,
            'resource_amount' => 50
        ]);

        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId, 'level' => 1, 'coin' => 100, 'exp' => 100]);
        UsrItem::factory()->create(['usr_user_id' => $usrUserId, 'mst_item_id' => $mstItem->getId(), 'amount' => 100]);
        UsrUnit::factory()->create(['usr_user_id' => $usrUserId, 'grade_level' => 5]);
        $this->createDiamond($usrUserId, 100);

        // ユーザの図鑑ランクを取得するためにUsrUnitSummaryを作成
        UsrUnitSummary::factory()->create([
            'usr_user_id' => $usrUserId,
            'grade_level_total_count' => 5,
        ]);

        // Exercise
        $rewardIds = $mstUnitEncyclopediaRewards->map(fn ($reward) => $reward->toEntity()->getId());
        $this->service->receiveReward($usrUserId, $rewardIds, UserConstant::PLATFORM_IOS, $now);
        $this->sendRewards($usrUserId, UserConstant::PLATFORM_IOS, $now);

        // Verify
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(2, $usrUserParameter->getLevel());
        $this->assertEquals(100 + 40, $usrUserParameter->getExp());
        // キャラ図鑑報酬 + レベルアップ報酬
        $this->assertEquals(100 + 10 + 50, $usrUserParameter->getCoin());

        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals(100 + 20, $diamond->getFreeAmount());

        $usrItem = UsrItem::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_item_id', $mstItem->getId())
            ->first();
        $this->assertEquals(100 + 30, $usrItem->getAmount());

        $usrEmblem = UsrEmblem::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_emblem_id', $mstEmblem->getId())
            ->first();
        $this->assertNotNull($usrEmblem);

        $usrReceivedUnitEncyclopediaRewards = UsrReceivedUnitEncyclopediaReward::query()
            ->where('usr_user_id', $usrUserId)
            ->whereIn('mst_unit_encyclopedia_reward_id', $rewardIds)
            ->get();
        $this->assertCount($rewardIds->count(), $usrReceivedUnitEncyclopediaRewards);
    }

    public function testLotteryArtworkFragment_原画のかけらドロップ抽選()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $artworkId = 'artwork1';
        $dropGroupId = 'drop_group1';

        MstArtwork::factory()->create(['id' => 'artwork1']);
        // ドロップ率を100%、一方を獲得済みにして重複ドロップでエラーにならないことを確認
        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment1', 'mst_artwork_id' => $artworkId, 'drop_group_id' => $dropGroupId, 'drop_percentage' => 100],
            ['id' => 'fragment2', 'mst_artwork_id' => $artworkId, 'drop_group_id' => $dropGroupId, 'drop_percentage' => 100],
            ['id' => 'fragment3', 'mst_artwork_id' => $artworkId, 'drop_group_id' => $dropGroupId, 'drop_percentage' => 50],
        ]);
        UsrArtworkFragment::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_artwork_id' => $artworkId,
            'mst_artwork_fragment_id' => 'fragment1',
        ]);

        // Exercise
        $dropRateMultiplier = 2;
        $this->execPrivateMethod(
            $this->service,
            'lotteryArtworkFragment',
            [$usrUserId, $dropGroupId, $dropRateMultiplier]
        );
        $this->saveAll();

        // Verify
        $usrArtworkFragments = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->whereIn('mst_artwork_fragment_id', ['fragment1', 'fragment2', 'fragment3'])
            ->get();
        $this->assertCount(3, $usrArtworkFragments);
    }

    public static function params_createArtworkIfComplete_原画データの登録()
    {
        return [
            '原画どちらも未完成' => [
                'mstArtworkIds' => collect(['artwork1', 'artwork2']),
                'usrArtworkFragments' => collect(),
                'usrArtworkIds' => collect(),
                'expectedArtworkIds' => collect(),
            ],
            '1つだけ完成' => [
                'mstArtworkIds' => collect(['artwork1', 'artwork2']),
                'usrArtworkFragments' => collect([
                    ['mstArtworkId' => 'artwork1', 'mstArtworkFragmentId' => 'fragment1-1'],
                    ['mstArtworkId' => 'artwork1', 'mstArtworkFragmentId' => 'fragment1-2'],
                ]),
                'usrArtworkIds' => collect(),
                'expectedArtworkIds' => collect(['artwork1']),
            ],
            '原画どちらも完成' => [
                'mstArtworkIds' => collect(['artwork1', 'artwork2']),
                'usrArtworkFragments' => collect([
                    ['mstArtworkId' => 'artwork1', 'mstArtworkFragmentId' => 'fragment1-1'],
                    ['mstArtworkId' => 'artwork1', 'mstArtworkFragmentId' => 'fragment1-2'],
                    ['mstArtworkId' => 'artwork2', 'mstArtworkFragmentId' => 'fragment2-1'],
                    ['mstArtworkId' => 'artwork2', 'mstArtworkFragmentId' => 'fragment2-2'],
                ]),
                'usrArtworkIds' => collect(),
                'expectedArtworkIds' => collect(['artwork1', 'artwork2']),
            ],
            // 完成している原画の原画のかけらを新たに取得することはないので実際に発生しないはずだが念の為テスト
            'どちらも原画データあり' => [
                'mstArtworkIds' => collect(['artwork1', 'artwork2']),
                'usrArtworkFragments' => collect(),
                'usrArtworkIds' => collect(['artwork1', 'artwork2']),
                'expectedArtworkIds' => collect(),
            ],
        ];
    }

    /**
     * @dataProvider params_createArtworkIfComplete_原画データの登録
     */
    public function testCreateArtworkIfComplete_原画データの登録(
        Collection $mstArtworkIds,
        Collection $usrArtworkFragments,
        Collection $usrArtworkIds,
        Collection $expectedArtworkIds,
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        MstArtwork::factory()->createMany([
            ['id' => 'artwork1'],
            ['id' => 'artwork2'],
        ]);
        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment1-1', 'mst_artwork_id' => 'artwork1'],
            ['id' => 'fragment1-2', 'mst_artwork_id' => 'artwork1'],
            ['id' => 'fragment2-1', 'mst_artwork_id' => 'artwork2'],
            ['id' => 'fragment2-2', 'mst_artwork_id' => 'artwork2'],
        ]);

        $usrArtworkFragments->each(function (array $usrArtworkFragment) use ($usrUserId) {
            UsrArtworkFragment::factory()->create([
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => $usrArtworkFragment['mstArtworkId'],
                'mst_artwork_fragment_id' => $usrArtworkFragment['mstArtworkFragmentId'],
            ]);
        });

        $usrArtworkIds->each(function (string $usrArtworkId) use ($usrUserId) {
            UsrArtwork::factory()->create([
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => $usrArtworkId,
            ]);
        });

        // Exercise
        $this->execPrivateMethod($this->service, 'createArtworkIfComplete', [$usrUserId, $mstArtworkIds]);
        $this->saveAll();

        // Verify
        $usrArtworks = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->whereIn('mst_artwork_id', $expectedArtworkIds)
            ->get()
            ->keyBy(fn($usrArtwork) => $usrArtwork->getMstArtworkId());
        $this->assertCount($expectedArtworkIds->count(), $usrArtworks);
        $expectedArtworkIds->each(function (string $expectedArtworkId) use ($usrArtworks) {
            $this->assertNotNull($usrArtworks->get($expectedArtworkId));
        });
    }

    public function test_acquireArtworkAndArtworkFragments_原画と原画のかけら獲得()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $mstArtworkId = 'artwork1';
        $dropGroupId = 'dropGroup1';

        MstArtwork::factory()->create(['id' => $mstArtworkId]);
        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment1', 'mst_artwork_id' => $mstArtworkId, 'drop_group_id' => $dropGroupId, 'drop_percentage' => 100],
            ['id' => 'fragment2', 'mst_artwork_id' => $mstArtworkId, 'drop_group_id' => $dropGroupId, 'drop_percentage' => 100],
        ]);

        // Exercise
        $this->service->acquireArtworkAndArtworkFragments(
            $usrUserId,
            InGameContentType::STAGE,
            'stage1',
            $dropGroupId,
            1,
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $usrArtworkFragments = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->whereIn('mst_artwork_fragment_id', ['fragment1', 'fragment2'])
            ->get();
        $this->assertCount(2, $usrArtworkFragments);

        $usrArtworks = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_id', $mstArtworkId)
            ->get();
        $this->assertCount(1, $usrArtworks);

        // ログの確認
        $actual = LogArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('content_type', InGameContentType::STAGE->value)
            ->where('target_id', 'stage1')
            ->whereIn('mst_artwork_fragment_id', ['fragment1', 'fragment2'])
            ->where('is_complete_artwork', 1)
            ->get();
        $this->assertCount(2, $actual);
    }

    public function test_acquireArtworkAndArtworkFragments_原画のかけらのみで原画は未獲得()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $mstArtworkId = 'artwork1';
        $dropGroupId = 'dropGroup1';

        MstArtwork::factory()->create(['id' => $mstArtworkId]);
        MstArtworkFragment::factory()->createMany([
            // ドロップは1つだけなので原画は完成しない
            ['id' => 'fragment1', 'mst_artwork_id' => $mstArtworkId, 'drop_group_id' => $dropGroupId, 'drop_percentage' => 100],
            ['id' => 'fragment2', 'mst_artwork_id' => $mstArtworkId, 'drop_group_id' => $dropGroupId, 'drop_percentage' => 0],
        ]);

        // Exercise
        $this->service->acquireArtworkAndArtworkFragments(
            $usrUserId,
            InGameContentType::STAGE,
            'stage1',
            $dropGroupId,
            1,
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $usrArtworkFragments = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->whereIn('mst_artwork_fragment_id', ['fragment1'])
            ->get();
        $this->assertCount(1, $usrArtworkFragments);

        $usrArtworks = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_id', $mstArtworkId)
            ->get();
        $this->assertCount(0, $usrArtworks);

        // ログの確認
        $actual = LogArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('content_type', InGameContentType::STAGE->value)
            ->where('target_id', 'stage1')
            ->whereIn('mst_artwork_fragment_id', ['fragment1'])
            ->where('is_complete_artwork', 0)
            ->get();
        $this->assertCount(1, $actual);
    }

    public function test_grantArtworksWithFragments_原画と全かけらを同時付与()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        MstArtwork::factory()->createMany([
            ['id' => 'artwork1'],
            ['id' => 'artwork2'],
        ]);
        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment1-1', 'mst_artwork_id' => 'artwork1'],
            ['id' => 'fragment1-2', 'mst_artwork_id' => 'artwork1'],
            ['id' => 'fragment2-1', 'mst_artwork_id' => 'artwork2'],
            ['id' => 'fragment2-2', 'mst_artwork_id' => 'artwork2'],
        ]);

        // Exercise
        $this->service->grantArtworksWithFragments(
            $usrUserId,
            collect(['artwork1', 'artwork2'])
        );
        $this->saveAll();

        // Verify - Artworkが付与されている
        $usrArtworks = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->whereIn('mst_artwork_id', ['artwork1', 'artwork2'])
            ->get();
        $this->assertCount(2, $usrArtworks);

        // Verify - 全Fragmentが付与されている
        $usrFragments = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->get();
        $this->assertCount(4, $usrFragments);
    }

    public function test_grantArtworksWithFragments_既存の原画は除外される()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        MstArtwork::factory()->createMany([
            ['id' => 'artwork1'],
            ['id' => 'artwork2'],
        ]);
        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment1-1', 'mst_artwork_id' => 'artwork1'],
            ['id' => 'fragment1-2', 'mst_artwork_id' => 'artwork1'],
            ['id' => 'fragment2-1', 'mst_artwork_id' => 'artwork2'],
            ['id' => 'fragment2-2', 'mst_artwork_id' => 'artwork2'],
        ]);

        // 既にartwork1を所持
        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_artwork_id' => 'artwork1',
        ]);
        UsrArtworkFragment::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => 'artwork1',
                'mst_artwork_fragment_id' => 'fragment1-1',
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => 'artwork1',
                'mst_artwork_fragment_id' => 'fragment1-2',
            ],
        ]);

        // Exercise
        $this->service->grantArtworksWithFragments(
            $usrUserId,
            collect(['artwork1', 'artwork2'])
        );
        $this->saveAll();

        // Verify - artwork1は既存、artwork2のみ新規付与
        $usrArtworks = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->whereIn('mst_artwork_id', ['artwork1', 'artwork2'])
            ->get();
        $this->assertCount(2, $usrArtworks);

        // Verify - artwork2のFragmentのみ新規付与（artwork1は既存の2つ）
        $usrFragments = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->get();
        $this->assertCount(4, $usrFragments); // artwork1: 2 + artwork2: 2
    }

    public function test_grantArtworksWithFragments_一部かけら所持の場合未所持のみ付与()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        MstArtwork::factory()->create(['id' => 'artwork1']);
        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment1-1', 'mst_artwork_id' => 'artwork1'],
            ['id' => 'fragment1-2', 'mst_artwork_id' => 'artwork1'],
            ['id' => 'fragment1-3', 'mst_artwork_id' => 'artwork1'],
        ]);

        // 既にfragment1-1を所持
        UsrArtworkFragment::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_artwork_id' => 'artwork1',
            'mst_artwork_fragment_id' => 'fragment1-1',
        ]);

        // Exercise
        $this->service->grantArtworksWithFragments(
            $usrUserId,
            collect(['artwork1'])
        );
        $this->saveAll();

        // Verify - Artworkが付与されている
        $usrArtwork = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_id', 'artwork1')
            ->first();
        $this->assertNotNull($usrArtwork);

        // Verify - 全Fragmentが揃っている（既存1 + 新規2）
        $usrFragments = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_id', 'artwork1')
            ->get();
        $this->assertCount(3, $usrFragments);
    }

    public function test_grantArtworksWithFragments_空のコレクションでは何もしない()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // Exercise
        $this->service->grantArtworksWithFragments(
            $usrUserId,
            collect()
        );
        $this->saveAll();

        // Verify - 何も付与されていない
        $usrArtworks = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->get();
        $this->assertCount(0, $usrArtworks);

        $usrFragments = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->get();
        $this->assertCount(0, $usrFragments);
    }
}
