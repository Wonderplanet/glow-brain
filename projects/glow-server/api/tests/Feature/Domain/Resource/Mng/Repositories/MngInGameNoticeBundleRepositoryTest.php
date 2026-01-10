<?php

namespace Feature\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Enums\Language;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Mng\Models\MngInGameNotice;
use App\Domain\Resource\Mng\Models\MngInGameNoticeI18n;
use App\Domain\Resource\Mng\Repositories\MngInGameNoticeBundleRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MngInGameNoticeBundleRepositoryTest extends TestCase
{
    private MngInGameNoticeBundleRepository $mngInGameNoticeBundleRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mngInGameNoticeBundleRepository = app()->make(MngInGameNoticeBundleRepository::class);
    }

    /**
     * テスト用のIGNデータを作成
     * 期間内2つ、期限切れ1つ、未来1つのデータを作成
     */
    private function createIgnMasterData(): void
    {
        // ノーティスデータを作成
        MngInGameNotice::factory()->createMany([
            // 期間内1
            [
                'id' => 'ign_001',
                'enable' => true,
                'priority' => 1,
                'start_at' => '2023-01-10 00:00:00',
                'end_at' => '2023-01-20 23:59:59',
            ],
            // 期間内2
            [
                'id' => 'ign_002',
                'enable' => true,
                'priority' => 2,
                'start_at' => '2023-01-12 00:00:00',
                'end_at' => '2023-01-18 23:59:59',
            ],
            // 期限切れ
            [
                'id' => 'ign_003',
                'enable' => true,
                'priority' => 3,
                'start_at' => '2023-01-01 00:00:00',
                'end_at' => '2023-01-08 23:59:59',
            ],
            // 未来
            [
                'id' => 'ign_004',
                'enable' => true,
                'priority' => 4,
                'start_at' => '2023-01-20 00:00:00',
                'end_at' => '2023-01-25 23:59:59',
            ],
            // 無効
            [
                'id' => 'ign_005',
                'enable' => false,
                'priority' => 5,
                'start_at' => '2023-01-10 00:00:00',
                'end_at' => '2023-01-20 23:59:59',
            ],
        ]);

        // 多言語データを作成
        MngInGameNoticeI18n::factory()->createMany([
            [
                'mng_in_game_notice_id' => 'ign_001',
                'language' => Language::Ja->value,
                'title' => 'ノーティス1',
            ],
            [
                'mng_in_game_notice_id' => 'ign_002',
                'language' => Language::Ja->value,
                'title' => 'ノーティス2',
            ],
            [
                'mng_in_game_notice_id' => 'ign_003',
                'language' => Language::Ja->value,
                'title' => 'ノーティス3',
            ],
            [
                'mng_in_game_notice_id' => 'ign_004',
                'language' => Language::Ja->value,
                'title' => 'ノーティス4',
            ],
            [
                'mng_in_game_notice_id' => 'ign_005',
                'language' => Language::Ja->value,
                'title' => 'ノーティス5',
            ],
        ]);
    }

    public function test_getActiveMngInGameNoticeBundlesByLanguage_キャッシュ動作確認_取得時間に応じて有効データが変わる(): void
    {
        // Setup
        $this->createIgnMasterData();
        $language = Language::Ja->value;
        $cacheKey = CacheKeyUtil::getMngInGameNoticeBundleKey($language);

        // キャッシュが空であることを確認
        $this->assertNull($this->getFromRedis($cacheKey));

        // sql発行回数
        $queryCount = 0;
        DB::listen(function ($query) use (&$queryCount) {
            $queryCount++;
        });

        // Exercise 1 - 初回実行でキャッシュ作成
        $now = $this->fixTime('2023-01-15 12:00:00');
        $result1 = $this->mngInGameNoticeBundleRepository->getActiveMngInGameNoticeBundlesByLanguage($language, $now);
        $this->assertEqualsCanonicalizing(['ign_001', 'ign_002'], $result1->keys()->toArray());
        // SQL発行回数が2回であることを確認（MngInGameNotice + MngInGameNoticeI18n）
        $this->assertEquals(2, $queryCount);
        // キャッシュが作成されていることを確認
        $this->assertNotNull($this->getFromRedis($cacheKey));

        // Exercise 2 - 2回目実行でキャッシュから取得
        $now = $this->fixTime('2023-01-20 12:00:00');
        $result2 = $this->mngInGameNoticeBundleRepository->getActiveMngInGameNoticeBundlesByLanguage($language, $now);
        // SQL発行回数が変わらないことを確認
        $this->assertEqualsCanonicalizing(['ign_001', 'ign_004'], $result2->keys()->toArray());

        // Exercise 3 - 3回目実行でキャッシュから取得
        $now = $this->fixTime('2023-01-23 12:00:00');
        $result3 = $this->mngInGameNoticeBundleRepository->getActiveMngInGameNoticeBundlesByLanguage($language, $now);
        // SQL発行回数が変わらないことを確認
        $this->assertEqualsCanonicalizing(['ign_004'], $result3->keys()->toArray());
    }

    public function test_deleteAllCache(): void
    {
        // Setup
        $this->createIgnMasterData();
        $now = $this->fixTime('2023-01-15 12:00:00');

        // 各言語でキャッシュを作成
        foreach (Language::cases() as $language) {
            $this->mngInGameNoticeBundleRepository->getActiveMngInGameNoticeBundlesByLanguage($language->value, $now);
            $cacheKey = CacheKeyUtil::getMngInGameNoticeBundleKey($language->value);
            $this->assertNotNull($this->getFromRedis($cacheKey));
        }

        // Execute: 全キャッシュ削除
        $this->mngInGameNoticeBundleRepository->deleteAllCache();

        // Verify: 全キャッシュが削除されていることを確認
        foreach (Language::cases() as $language) {
            $cacheKey = CacheKeyUtil::getMngInGameNoticeBundleKey($language->value);
            $this->assertNull($this->getFromRedis($cacheKey));
        }
    }
}
