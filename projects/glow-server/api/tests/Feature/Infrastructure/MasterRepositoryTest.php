<?php

namespace Feature\Domain\Item\Repositories;

use App\Domain\Resource\Mst\Models\MstEvent;
use App\Infrastructure\MasterRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MasterRepositoryTest extends TestCase
{
    private MasterRepository $masterRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->masterRepository = app(MasterRepository::class);
    }

    private function createMstDataForJst20250128()
    {
        // マスタデータの時間情報はUTCで指定
        MstEvent::factory()->createMany([
            //    <|----------|> 今日：JSTで 2025-01-28 00:00:00 〜 2025-01-28 23:59:59。UTCで 2025-01-27 15:00:00 〜 2025-01-28 14:59:59
            // -  : 対象期間中を示す
            // |  : 対象期間の左端または右端を示す
            // <  : 今日の始まりの日時を示す(00:00:00)
            // >  : 今日の終わりの日時を示す(23:59:59)
            // <| : 対象開始日時が今日の始まりと同じ
            // |> : 対象終了日時が今日の終わりと同じ

            // 開始日時が前日以前
            // |---| <     >
            ['id' => 'event1-1', 'start_at' => '2025-01-26 15:00:00', 'end_at' => '2025-01-27 14:00:00'],
            // |-----<|    >
            ['id' => 'event1-2', 'start_at' => '2025-01-26 15:00:00', 'end_at' => '2025-01-27 15:00:00'],
            // |-----<--|-->
            ['id' => 'event1-3', 'start_at' => '2025-01-26 15:00:00', 'end_at' => '2025-01-27 16:00:00'],
            // |-----<----|>
            ['id' => 'event1-4', 'start_at' => '2025-01-26 15:00:00', 'end_at' => '2025-01-28 14:59:59'],
            // |-----<----->---|
            ['id' => 'event1-5', 'start_at' => '2025-01-26 15:00:00', 'end_at' => '2025-01-29 14:59:59'],

            // 開始日時が今日の始まりと同じ
            // <|--| >
            ['id' => 'event2-1', 'start_at' => '2025-01-27 15:00:00', 'end_at' => '2025-01-27 16:00:00'],
            // <|---|>
            ['id' => 'event2-2', 'start_at' => '2025-01-27 15:00:00', 'end_at' => '2025-01-28 14:59:59'],
            // <|---->---|
            ['id' => 'event2-3', 'start_at' => '2025-01-27 15:00:00', 'end_at' => '2025-01-29 14:59:59'],

            // 開始日時が今日の始まりから今日の終わりの間
            // <  |--| >
            ['id' => 'event3-1', 'start_at' => '2025-01-27 16:00:00', 'end_at' => '2025-01-27 17:00:00'],
            // <  |---|>
            ['id' => 'event3-2', 'start_at' => '2025-01-27 16:00:00', 'end_at' => '2025-01-28 14:59:59'],
            // <  |---->--|
            ['id' => 'event3-3', 'start_at' => '2025-01-27 16:00:00', 'end_at' => '2025-01-29 14:59:59'],

            // 開始日時が今日の終わり以降
            // < |>---|
            ['id' => 'event4-1', 'start_at' => '2025-01-28 14:59:59', 'end_at' => '2025-01-29 14:59:59'],
            // <  > |--|
            ['id' => 'event4-2', 'start_at' => '2025-01-29 14:59:59', 'end_at' => '2025-01-29 16:59:59'],
        ]);
    }

    public function test_getDayActives_1日分のキャッシュを作ってデータ取得できる()
    {
        // Setup
        $now = $this->fixTime('2025-01-27 15:00:00'); // JST: 2025-01-28 00:00:00

        $this->createMstDataForJst20250128();

        // Exercise
        $result = $this->masterRepository->getDayActives(MstEvent::class, $now);

        // Verify
        $this->assertEqualsCanonicalizing(
            [
                'event1-2',
                'event1-3',
                'event1-4',
                'event1-5',
                'event2-1',
                'event2-2',
                'event2-3',
                'event3-1',
                'event3-2',
                'event3-3',
                'event4-1',
            ],
            $result->keys()->toArray(),
        );
    }

    public static function params_test_getDayActives_日跨ぎすればキャッシュを作り直してデータ取得する()
    {
        return [
            '日跨ぎしてキャッシュが作り直されてキャッシュが2つになっている' => [
                'nowUtc' => '2025-01-28 15:00:00', // 1回目のデータ取得から1日後
                'expected' => 2,
            ],
            '日跨ぎせずキャッシュ作り直ししないのでキャッシュは1つのまま' => [
                'nowUtc' => '2025-01-27 16:00:00', // 1回目のデータ取得から1時間後
                'expected' => 1,
            ],
        ];
    }

    #[DataProvider('params_test_getDayActives_日跨ぎすればキャッシュを作り直してデータ取得する')]
    public function test_getDayActives_日跨ぎすればキャッシュを作り直してデータ取得する(string $nowUtc, int $expected)
    {
        if (ini_get('apc.enable_cli') != 1) {
            self::markTestSkipped('APCu is not enabled for CLI.');
        }

        // Setup
        apcu_clear_cache();
        $this->createMstDataForJst20250128();

        // Exercise

        // JST: 2025-01-28 00:00:00
        $now = $this->fixTime('2025-01-27 15:00:00');
        $result = $this->masterRepository->getDayActives(MstEvent::class, $now);
        $cache1 = apcu_cache_info()['cache_list'];

        // JST: 2025-01-29 00:00:00
        $now = $this->fixTime($nowUtc);
        $result = $this->masterRepository->getDayActives(MstEvent::class, $now);
        $cache2 = apcu_cache_info()['cache_list'];

        // Verify
        $this->assertCount(1, $cache1);
        $this->assertCount($expected, $cache2);
    }
}
