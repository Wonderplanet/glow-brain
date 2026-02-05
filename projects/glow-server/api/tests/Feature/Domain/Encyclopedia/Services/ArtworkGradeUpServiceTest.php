<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Encyclopedia\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Encyclopedia\Models\LogArtworkGradeUp;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Encyclopedia\Services\ArtworkGradeUpService;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstArtworkGradeUp;
use App\Domain\Resource\Mst\Models\MstArtworkGradeUpCost;
use App\Domain\Resource\Mst\Models\MstItem;
use Tests\TestCase;

class ArtworkGradeUpServiceTest extends TestCase
{
    private ArtworkGradeUpService $artworkGradeUpService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artworkGradeUpService = $this->app->make(ArtworkGradeUpService::class);
    }

    /**
     * 正常系: 通常アイテムでグレードアップできる
     */
    public function testGradeUp_通常アイテムでグレードアップできる(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $mstArtwork = MstArtwork::factory()->create();
        $mstItem = MstItem::factory()->create();

        $mstArtworkGradeUp = MstArtworkGradeUp::factory()->create([
            'mst_series_id' => $mstArtwork->mst_series_id,
            'rarity' => $mstArtwork->rarity,
            'grade_level' => 1,
        ]);
        MstArtworkGradeUpCost::factory()->create([
            'mst_artwork_grade_up_id' => $mstArtworkGradeUp->id,
            'resource_id' => $mstItem->id,
            'resource_amount' => 10,
        ]);

        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_artwork_id' => $mstArtwork->id,
            'grade_level' => 0,
        ]);

        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem->id,
            'amount' => 10,
        ]);

        // Act
        $result = $this->artworkGradeUpService->gradeUp($usrUser->getId(), $mstArtwork->id);
        $this->saveAll();

        // Assert
        $this->assertEquals(1, $result->getGradeLevel());

        $updatedUsrArtwork = UsrArtwork::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_artwork_id', $mstArtwork->id)
            ->first();
        $this->assertEquals(1, $updatedUsrArtwork->getGradeLevel());

        $usrItem = UsrItem::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_item_id', $mstItem->id)
            ->first();
        $this->assertEquals(0, $usrItem->getAmount());
    }

    /**
     * 正常系: 複数のコストが必要な場合も正常に実行できる
     */
    public function testGradeUp_複数コストでグレードアップできる(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $mstArtwork = MstArtwork::factory()->create();
        $mstItem1 = MstItem::factory()->create();
        $mstItem2 = MstItem::factory()->create();

        $mstArtworkGradeUp = MstArtworkGradeUp::factory()->create([
            'mst_series_id' => $mstArtwork->mst_series_id,
            'rarity' => $mstArtwork->rarity,
            'grade_level' => 1,
        ]);
        MstArtworkGradeUpCost::factory()->create([
            'mst_artwork_grade_up_id' => $mstArtworkGradeUp->id,
            'resource_id' => $mstItem1->id,
            'resource_amount' => 5,
        ]);
        MstArtworkGradeUpCost::factory()->create([
            'mst_artwork_grade_up_id' => $mstArtworkGradeUp->id,
            'resource_id' => $mstItem2->id,
            'resource_amount' => 3,
        ]);

        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_artwork_id' => $mstArtwork->id,
            'grade_level' => 0,
        ]);

        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem1->id,
            'amount' => 5,
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem2->id,
            'amount' => 3,
        ]);

        // Act
        $result = $this->artworkGradeUpService->gradeUp($usrUser->getId(), $mstArtwork->id);
        $this->saveAll();

        // Assert
        $this->assertEquals(1, $result->getGradeLevel());

        $usrItem1 = UsrItem::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_item_id', $mstItem1->id)
            ->first();
        $this->assertEquals(0, $usrItem1->getAmount());

        $usrItem2 = UsrItem::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_item_id', $mstItem2->id)
            ->first();
        $this->assertEquals(0, $usrItem2->getAmount());
    }

    /**
     * 正常系: グレードアップログが作成される
     */
    public function testGradeUp_ログが作成される(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $mstArtwork = MstArtwork::factory()->create();
        $mstItem = MstItem::factory()->create();

        $mstArtworkGradeUp = MstArtworkGradeUp::factory()->create([
            'mst_series_id' => $mstArtwork->mst_series_id,
            'rarity' => $mstArtwork->rarity,
            'grade_level' => 1,
        ]);
        MstArtworkGradeUpCost::factory()->create([
            'mst_artwork_grade_up_id' => $mstArtworkGradeUp->id,
            'resource_id' => $mstItem->id,
            'resource_amount' => 5,
        ]);

        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_artwork_id' => $mstArtwork->id,
            'grade_level' => 0,
        ]);

        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem->id,
            'amount' => 5,
        ]);

        // Act
        $this->artworkGradeUpService->gradeUp($usrUser->getId(), $mstArtwork->id);
        $this->saveAll();
        $this->saveAllLogModel();

        // Assert
        $logArtworkGradeUp = LogArtworkGradeUp::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_artwork_id', $mstArtwork->id)
            ->first();

        $this->assertNotNull($logArtworkGradeUp);
        $this->assertEquals(0, $logArtworkGradeUp->before_grade_level);
        $this->assertEquals(1, $logArtworkGradeUp->after_grade_level);
    }

    /**
     * 正常系: 連続でグレードアップできる (grade 0 → 1 → 2)
     */
    public function testGradeUp_連続でグレードアップできる(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $mstArtwork = MstArtwork::factory()->create();
        $mstItem = MstItem::factory()->create();

        // Grade 1 と Grade 2 のマスタを作成
        $mstArtworkGradeUp1 = MstArtworkGradeUp::factory()->create([
            'mst_series_id' => $mstArtwork->mst_series_id,
            'rarity' => $mstArtwork->rarity,
            'grade_level' => 1,
        ]);
        $mstArtworkGradeUp2 = MstArtworkGradeUp::factory()->create([
            'mst_series_id' => $mstArtwork->mst_series_id,
            'rarity' => $mstArtwork->rarity,
            'grade_level' => 2,
        ]);

        MstArtworkGradeUpCost::factory()->create([
            'mst_artwork_grade_up_id' => $mstArtworkGradeUp1->id,
            'resource_id' => $mstItem->id,
            'resource_amount' => 5,
        ]);
        MstArtworkGradeUpCost::factory()->create([
            'mst_artwork_grade_up_id' => $mstArtworkGradeUp2->id,
            'resource_id' => $mstItem->id,
            'resource_amount' => 10,
        ]);

        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_artwork_id' => $mstArtwork->id,
            'grade_level' => 0,
        ]);

        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem->id,
            'amount' => 15, // 5 + 10 = 15
        ]);

        // Act: 1回目のグレードアップ (0 → 1)
        $result1 = $this->artworkGradeUpService->gradeUp($usrUser->getId(), $mstArtwork->id);
        $this->saveAll();

        // Assert
        $this->assertEquals(1, $result1->getGradeLevel());

        // Act: 2回目のグレードアップ (1 → 2)
        $result2 = $this->artworkGradeUpService->gradeUp($usrUser->getId(), $mstArtwork->id);
        $this->saveAll();

        // Assert
        $this->assertEquals(2, $result2->getGradeLevel());

        $usrItem = UsrItem::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_item_id', $mstItem->id)
            ->first();
        $this->assertEquals(0, $usrItem->getAmount());
    }

    /**
     * 異常系: 原画を所持していない場合エラーになる
     */
    public function testGradeUp_原画未所持でエラー(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $mstArtwork = MstArtwork::factory()->create();

        // Assert
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ARTWORK_NOT_OWNED);

        // Act
        $this->artworkGradeUpService->gradeUp($usrUser->getId(), $mstArtwork->id);
    }

    /**
     * 異常系: 最大グレードに達している場合エラーになる
     */
    public function testGradeUp_最大グレード到達でエラー(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $mstArtwork = MstArtwork::factory()->create();

        // Grade 1のマスタのみ作成（Grade 2は存在しない）
        MstArtworkGradeUp::factory()->create([
            'mst_series_id' => $mstArtwork->mst_series_id,
            'rarity' => $mstArtwork->rarity,
            'grade_level' => 1,
        ]);

        // すでにgrade 1の原画を所持
        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_artwork_id' => $mstArtwork->id,
            'grade_level' => 1,
        ]);


        // Assert
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);

        // Act
        $this->artworkGradeUpService->gradeUp($usrUser->getId(), $mstArtwork->id);
    }

    /**
     * 異常系: 必要コストを所持していない場合エラーになる
     */
    public function testGradeUp_アイテム不足でエラー(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $mstArtwork = MstArtwork::factory()->create();
        $mstItem = MstItem::factory()->create();

        $mstArtworkGradeUp = MstArtworkGradeUp::factory()->create([
            'mst_series_id' => $mstArtwork->mst_series_id,
            'rarity' => $mstArtwork->rarity,
            'grade_level' => 1,
        ]);
        MstArtworkGradeUpCost::factory()->create([
            'mst_artwork_grade_up_id' => $mstArtworkGradeUp->id,
            'resource_id' => $mstItem->id,
            'resource_amount' => 10,
        ]);

        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_artwork_id' => $mstArtwork->id,
            'grade_level' => 0,
        ]);

        // 必要数10に対して9しか持っていない
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem->id,
            'amount' => 9,
        ]);


        // Assert
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ITEM_AMOUNT_IS_NOT_ENOUGH);

        // Act
        $this->artworkGradeUpService->gradeUp($usrUser->getId(), $mstArtwork->id);
    }

    /**
     * 異常系: コストが設定されていない場合エラーになる
     */
    public function testGradeUp_コスト未設定でエラー(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $mstArtwork = MstArtwork::factory()->create();

        // コストなしでグレードアップマスタを作成
        MstArtworkGradeUp::factory()->create([
            'mst_series_id' => $mstArtwork->mst_series_id,
            'rarity' => $mstArtwork->rarity,
            'grade_level' => 1,
        ]);

        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_artwork_id' => $mstArtwork->id,
            'grade_level' => 0,
        ]);


        // Assert
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);

        // Act
        $this->artworkGradeUpService->gradeUp($usrUser->getId(), $mstArtwork->id);
    }

    /**
     * 正常系: 原画固有設定がある場合はそちらが使われる
     */
    public function testGradeUp_原画固有設定がある場合はそちらが使われる(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $mstArtwork = MstArtwork::factory()->create();
        $mstDefaultItem = MstItem::factory()->create();
        $mstSpecificItem = MstItem::factory()->create();

        // デフォルト行（mst_artwork_id = null）
        $mstArtworkGradeUpDefault = MstArtworkGradeUp::factory()->create([
            'mst_artwork_id' => null,
            'mst_series_id' => $mstArtwork->mst_series_id,
            'rarity' => $mstArtwork->rarity,
            'grade_level' => 1,
        ]);
        MstArtworkGradeUpCost::factory()->create([
            'mst_artwork_grade_up_id' => $mstArtworkGradeUpDefault->id,
            'resource_id' => $mstDefaultItem->id,
            'resource_amount' => 10,
        ]);

        // 原画個別行（mst_artwork_id = 対象原画ID）
        $mstArtworkGradeUpSpecific = MstArtworkGradeUp::factory()->create([
            'mst_artwork_id' => $mstArtwork->id,
            'mst_series_id' => $mstArtwork->mst_series_id,
            'rarity' => $mstArtwork->rarity,
            'grade_level' => 1,
        ]);
        MstArtworkGradeUpCost::factory()->create([
            'mst_artwork_grade_up_id' => $mstArtworkGradeUpSpecific->id,
            'resource_id' => $mstSpecificItem->id,
            'resource_amount' => 5,
        ]);

        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_artwork_id' => $mstArtwork->id,
            'grade_level' => 0,
        ]);

        // デフォルトアイテムと個別アイテムの両方を所持
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstDefaultItem->id,
            'amount' => 10,
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstSpecificItem->id,
            'amount' => 5,
        ]);

        // Act
        $result = $this->artworkGradeUpService->gradeUp($usrUser->getId(), $mstArtwork->id);
        $this->saveAll();

        // Assert
        $this->assertEquals(1, $result->getGradeLevel());

        // 個別設定のアイテム（5個）が消費されていること
        $usrSpecificItem = UsrItem::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_item_id', $mstSpecificItem->id)
            ->first();
        $this->assertEquals(0, $usrSpecificItem->getAmount());

        // デフォルトアイテムは消費されていないこと
        $usrDefaultItem = UsrItem::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_item_id', $mstDefaultItem->id)
            ->first();
        $this->assertEquals(10, $usrDefaultItem->getAmount());
    }

    /**
     * 正常系: 原画固有設定がない場合はレアリティデフォルトが使われる
     */
    public function testGradeUp_原画固有設定がない場合はレアリティデフォルトが使われる(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $mstArtwork = MstArtwork::factory()->create();
        $mstItem = MstItem::factory()->create();

        // デフォルト行のみ作成（mst_artwork_id = null）
        $mstArtworkGradeUp = MstArtworkGradeUp::factory()->create([
            'mst_artwork_id' => null,
            'mst_series_id' => $mstArtwork->mst_series_id,
            'rarity' => $mstArtwork->rarity,
            'grade_level' => 1,
        ]);
        MstArtworkGradeUpCost::factory()->create([
            'mst_artwork_grade_up_id' => $mstArtworkGradeUp->id,
            'resource_id' => $mstItem->id,
            'resource_amount' => 10,
        ]);

        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_artwork_id' => $mstArtwork->id,
            'grade_level' => 0,
        ]);

        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem->id,
            'amount' => 10,
        ]);

        // Act
        $result = $this->artworkGradeUpService->gradeUp($usrUser->getId(), $mstArtwork->id);
        $this->saveAll();

        // Assert: フォールバックでデフォルト設定が使われること
        $this->assertEquals(1, $result->getGradeLevel());

        $usrItem = UsrItem::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_item_id', $mstItem->id)
            ->first();
        $this->assertEquals(0, $usrItem->getAmount());
    }
}
