<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Encyclopedia\Services;

use App\Domain\Encyclopedia\Constants\EncyclopediaConstant;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Encyclopedia\Services\ArtworkConvertService;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\User\Models\UsrUserParameter;
use Tests\Feature\Domain\Reward\Test1Reward;
use Tests\TestCase;

class ArtworkConvertServiceTest extends TestCase
{
    private ArtworkConvertService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(ArtworkConvertService::class);
    }

    /**
     * 重複した原画報酬をコインに変換できる
     */
    public function test_convertDuplicatedArtworkToCoin_重複原画報酬をコインに変換できる(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        // mst
        MstArtwork::factory()->createMany([
            ['id' => 'artwork1'],
            ['id' => 'artwork2'],
            ['id' => 'artwork3'],
            ['id' => 'artwork4'],
            ['id' => 'artwork5'],
        ]);
        MstItem::factory()->createMany([
            ['id' => 'item1'],
            ['id' => 'item2'],
        ]);

        // usr
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId, 'coin' => 1,]);
        UsrArtwork::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_artwork_id' => 'artwork3'],
            ['usr_user_id' => $usrUserId, 'mst_artwork_id' => 'artwork4'],
        ]);

        $rewards = collect([
            // artwork
            new Test1Reward(RewardType::ARTWORK, 'artwork1', 1),
            new Test1Reward(RewardType::ARTWORK, 'artwork2', 2),
            new Test1Reward(RewardType::ARTWORK, 'artwork3', 3),
            new Test1Reward(RewardType::ARTWORK, 'artwork4', 4),
            //   同じartwork5報酬を複数のRewardインスタンスに分けて設定
            new Test1Reward(RewardType::ARTWORK, 'artwork5', 1),
            new Test1Reward(RewardType::ARTWORK, 'artwork5', 1),
            new Test1Reward(RewardType::ARTWORK, 'artwork5', 3),
            // artwork以外の変換されないリソース
            new Test1Reward(RewardType::ITEM, 'item1', 1),
            new Test1Reward(RewardType::ITEM, 'item2', 2),
        ])->keyBy->getId();

        // Exercise
        $this->service->convertDuplicatedArtworkToCoin($usrUserId, $rewards);

        // Verify
        $this->assertCount(10, $rewards);

        $convertAmount = EncyclopediaConstant::DUPLICATE_ARTWORK_CONVERT_COIN;
        $this->assertEqualsCanonicalizing([
            // 初獲得
            'Artwork-artwork1-1',
            'Artwork-artwork2-1',
            'Artwork-artwork5-1',
            // 重複獲得はコインへ変換されている
            'Coin--' . (2 - 1) * $convertAmount,
            'Coin--' . 3 * $convertAmount,
            'Coin--' . 4 * $convertAmount,
            'Coin--' . 1 * $convertAmount,
            'Coin--' . 3 * $convertAmount,
            // artwork以外の変換されないリソースはそのまま
            'Item-item1-1',
            'Item-item2-2',
        ], $rewards->map(function (Test1Reward $reward) {
            return $reward->getType() . '-' . ($reward->getResourceId() ?? '') . '-' . $reward->getAmount();
        })->values()->toArray());
    }

    /**
     * 報酬が空の場合は何もしない
     */
    public function test_convertDuplicatedArtworkToCoin_報酬が空の場合は何もしない(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $rewards = collect();

        // Exercise
        $this->service->convertDuplicatedArtworkToCoin($usrUserId, $rewards);

        // Verify
        $this->assertCount(0, $rewards);
    }

    /**
     * 原画以外の報酬は変換しない
     */
    public function test_convertDuplicatedArtworkToCoin_原画以外の報酬は変換しない(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        MstItem::factory()->createMany([
            ['id' => 'item1'],
            ['id' => 'item2'],
        ]);

        $rewards = collect([
            new Test1Reward(RewardType::ITEM, 'item1', 1),
            new Test1Reward(RewardType::ITEM, 'item2', 2),
            new Test1Reward(RewardType::COIN, null, 100),
        ])->keyBy->getId();

        // Exercise
        $this->service->convertDuplicatedArtworkToCoin($usrUserId, $rewards);

        // Verify
        $this->assertCount(3, $rewards);
        $this->assertEqualsCanonicalizing([
            'Item-item1-1',
            'Item-item2-2',
            'Coin--100',
        ], $rewards->map(function (Test1Reward $reward) {
            return $reward->getType() . '-' . ($reward->getResourceId() ?? '') . '-' . $reward->getAmount();
        })->values()->toArray());
    }

    /**
     * 重複している無効なマスタデータの原画は配布対象から除外される
     */
    public function test_convertDuplicatedArtworkToCoin_無効なマスタデータの重複原画は除外される(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        // artwork1のみ有効なマスタデータ
        MstArtwork::factory()->create(['id' => 'artwork1']);

        // artwork1は所持済み（重複扱いになる）
        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_artwork_id' => 'artwork1',
        ]);

        $rewards = collect([
            // 有効なマスタデータ（所持済み）→ コイン変換
            new Test1Reward(RewardType::ARTWORK, 'artwork1', 1),
            // 無効なマスタデータで重複判定（amount > 1）→ 配布対象から除外
            new Test1Reward(RewardType::ARTWORK, 'invalid_artwork', 2),
        ])->keyBy->getId();

        // Exercise
        $this->service->convertDuplicatedArtworkToCoin($usrUserId, $rewards);

        // Verify
        // invalid_artworkはamount=2だったので初獲得分として1を分離後、残り1がforget対象
        // 結果: artwork1(coin変換) + invalid_artwork(初獲得分1のみ残る)
        $this->assertCount(2, $rewards);
        $convertAmount = EncyclopediaConstant::DUPLICATE_ARTWORK_CONVERT_COIN;
        $this->assertEqualsCanonicalizing([
            'Coin--' . $convertAmount,
            'Artwork-invalid_artwork-1', // 初獲得分は残る
        ], $rewards->map(function (Test1Reward $reward) {
            return $reward->getType() . '-' . ($reward->getResourceId() ?? '') . '-' . $reward->getAmount();
        })->values()->toArray());
    }

    // NOTE: ミッショントリガーのテストは MissionAllCriterionTest::test_rewardSendService_sendRewards_トリガーと進捗集約値の確認 に移行済み
}
