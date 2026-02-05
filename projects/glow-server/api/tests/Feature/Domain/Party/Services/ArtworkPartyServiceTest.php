<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Party\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Party\Constants\PartyConstant;
use App\Domain\Party\Models\Eloquent\UsrArtworkParty;
use App\Domain\Party\Services\ArtworkPartyService;
use App\Domain\Resource\Mst\Models\MstArtwork;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ArtworkPartyServiceTest extends TestCase
{
    private ArtworkPartyService $artworkPartyService;

    public function setUp(): void
    {
        parent::setUp();
        $this->artworkPartyService = $this->app->make(ArtworkPartyService::class);
    }

    public function test_saveParty_パーティが正常に保存できる(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();

        // 原画を作成
        $mstArtworkId1 = 'artwork_test_0001';
        $mstArtworkId2 = 'artwork_test_0002';
        $this->createArtwork($usrUserId, $mstArtworkId1);
        $this->createArtwork($usrUserId, $mstArtworkId2);

        // Act
        $result = $this->artworkPartyService->saveParty($usrUserId, [$mstArtworkId1, $mstArtworkId2]);
        $this->saveAll();

        // Assert
        $this->assertEquals($mstArtworkId1, $result->getMstArtworkId1());
        $this->assertEquals($mstArtworkId2, $result->getMstArtworkId2());

        // DBに保存されていることを確認
        $usrArtworkParty = UsrArtworkParty::query()
            ->where('usr_user_id', $usrUserId)
            ->first();
        $this->assertNotNull($usrArtworkParty);
        $this->assertEquals($mstArtworkId1, $usrArtworkParty->getMstArtworkId1());
        $this->assertEquals($mstArtworkId2, $usrArtworkParty->getMstArtworkId2());
    }

    public function test_saveParty_既存パーティがある場合は更新される(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();

        // 既存パーティを作成
        UsrArtworkParty::factory()->create([
            'usr_user_id' => $usrUserId,
            'party_no' => 1,
            'mst_artwork_id_1' => 'old_artwork',
            'mst_artwork_id_2' => 'old_artwork_2',
        ]);

        // 原画を作成
        $mstArtworkId = 'artwork_test_new';
        $this->createArtwork($usrUserId, $mstArtworkId);

        // Act
        $result = $this->artworkPartyService->saveParty($usrUserId, [$mstArtworkId]);
        $this->saveAll();

        // Assert
        $this->assertEquals($mstArtworkId, $result->getMstArtworkId1());

        // DBが更新されていることを確認
        $usrArtworkParty = UsrArtworkParty::query()
            ->where('usr_user_id', $usrUserId)
            ->first();
        $this->assertEquals($mstArtworkId, $usrArtworkParty->getMstArtworkId1());
        $this->assertNull($usrArtworkParty->getMstArtworkId2());
    }

    public static function params_validateArtworkIds_重複検証(): array
    {
        return [
            '重複なし' => [
                'mstArtworkIds' => ['artwork_1', 'artwork_2', 'artwork_3'],
                'expectedErrorCode' => null,
            ],
            '重複あり' => [
                'mstArtworkIds' => ['artwork_1', 'artwork_1', 'artwork_2'],
                'expectedErrorCode' => ErrorCode::PARTY_DUPLICATE_ARTWORK_ID,
            ],
        ];
    }

    #[DataProvider('params_validateArtworkIds_重複検証')]
    public function test_validateArtworkIds_重複検証(array $mstArtworkIds, ?int $expectedErrorCode): void
    {
        if ($expectedErrorCode !== null) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($expectedErrorCode);
        }

        $this->execPrivateMethod(
            $this->artworkPartyService,
            'validateArtworkIds',
            [collect($mstArtworkIds)]
        );

        $this->assertTrue(true);
    }

    public function test_validateOwned_所持している原画は検証を通過(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();

        $mstArtworkId = 'artwork_test_owned';
        $this->createArtwork($usrUserId, $mstArtworkId);

        // Act & Assert - エラーが発生しないことを確認
        $this->execPrivateMethod(
            $this->artworkPartyService,
            'validateOwned',
            [$usrUserId, collect([$mstArtworkId])]
        );

        $this->assertTrue(true);
    }

    public function test_validateOwned_所持していない原画がある場合はエラー(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();

        // MstArtworkは存在するが、UsrArtworkは持っていない状態
        $mstArtworkId = 'artwork_test_not_owned';
        MstArtwork::factory()->create(['id' => $mstArtworkId]);
        // UsrArtworkを作成しない

        // Act & Assert
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::PARTY_INVALID_ARTWORK_ID);

        $this->execPrivateMethod(
            $this->artworkPartyService,
            'validateOwned',
            [$usrUserId, collect([$mstArtworkId])]
        );
    }

    public static function params_validateArtworkIds_件数検証(): array
    {
        return [
            '上限以下' => [
                'count' => PartyConstant::MAX_ARTWORK_COUNT_IN_PARTY,
                'expectedErrorCode' => null,
            ],
            '上限超過' => [
                'count' => PartyConstant::MAX_ARTWORK_COUNT_IN_PARTY + 1,
                'expectedErrorCode' => ErrorCode::PARTY_INVALID_ARTWORK_COUNT,
            ],
            '空配列はエラー' => [
                'count' => 0,
                'expectedErrorCode' => ErrorCode::PARTY_INVALID_ARTWORK_COUNT,
            ],
        ];
    }

    #[DataProvider('params_validateArtworkIds_件数検証')]
    public function test_validateArtworkIds_件数検証(int $count, ?int $expectedErrorCode): void
    {
        $mstArtworkIds = collect();
        for ($i = 0; $i < $count; $i++) {
            $mstArtworkIds->push("artwork_{$i}");
        }

        if ($expectedErrorCode !== null) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($expectedErrorCode);
        }

        $this->execPrivateMethod(
            $this->artworkPartyService,
            'validateArtworkIds',
            [$mstArtworkIds]
        );

        $this->assertTrue(true);
    }

    /**
     * テスト用に原画を作成するヘルパーメソッド
     * 原画を所持している状態を作成する
     */
    private function createArtwork(string $usrUserId, string $mstArtworkId): void
    {
        MstArtwork::factory()->create(['id' => $mstArtworkId]);

        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_artwork_id' => $mstArtworkId,
        ]);
    }
}
