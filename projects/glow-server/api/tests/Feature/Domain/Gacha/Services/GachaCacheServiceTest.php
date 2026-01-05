<?php

namespace Feature\Domain\Gacha\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Gacha\Constants\GachaConstants;
use App\Domain\Gacha\Entities\GachaHistory;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Enums\UpperType;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\Services\GachaCacheService;
use App\Domain\Gacha\Services\GachaService;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Resource\Mst\Models\OprGachaUpper;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GachaCacheServiceTest extends TestCase
{
    private GachaCacheService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(GachaCacheService::class);
    }

    public static function params_testPrependGachaHistory_ガシャ履歴が追加できていること()
    {
        return [
            'キャッシュ無し' => [
                'beforeCacheCount' => 0,
                'expected' => 1,
            ],
            '追加しても上限を超えない' => [
                'beforeCacheCount' => 10,
                'expected' => 11,
            ],
            '追加で上限と同数' => [
                'beforeCacheCount' => GachaConstants::GACHA_HISTORY_LIMIT - 1,
                'expected' => GachaConstants::GACHA_HISTORY_LIMIT,
            ],
            '追加で上限を超える' => [
                'beforeCacheCount' => GachaConstants::GACHA_HISTORY_LIMIT,
                'expected' => GachaConstants::GACHA_HISTORY_LIMIT,
            ],
        ];
    }

    #[DataProvider('params_testPrependGachaHistory_ガシャ履歴が追加できていること')]
    public function testPrependGachaHistory_ガシャ履歴が追加できていること(int $beforeCacheCount, int $expected)
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = 'user_1';

        $gachaHistories = collect();
        for ($i = 0; $i < $beforeCacheCount; $i++) {
            $oprGachaId = "gacha_$i";
            $gachaHistories->add(
                $this->makeGachaHistory($oprGachaId, $now)
            );
        }
        $cacheKey = CacheKeyUtil::getGachaHistoryKey($usrUserId);
        $this->setToRedis($cacheKey, $gachaHistories);

        $gachaHistory = $this->makeGachaHistory('gacha_100', $now);

        // Exercise
        $this->service->prependGachaHistory($usrUserId, $gachaHistory);

        // Verify
        $actual = $this->getFromRedis($cacheKey);
        $this->assertCount($expected, $actual);

        // ttlが設定されていること
        $ttl = Redis::connection()->ttl($cacheKey);
        $this->assertEquals(GachaConstants::HISTORY_DAYS * 24 * 60 * 60, $ttl);
    }

    public function testPrependGachaHistory_追加により上限を超える場合は末尾のものが除外されること()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = 'user_1';
        $newHistoryOprGachaId = 'gacha_100';

        $expectedOprGachaIds = [];
        $gachaHistories = collect();
        for ($i = 0; $i < GachaConstants::GACHA_HISTORY_LIMIT; $i++) {
            $oprGachaId = "gacha_$i";
            $gachaHistories->add(
                $this->makeGachaHistory($oprGachaId, $now)
            );
            $expectedOprGachaIds[] = $oprGachaId;
        }
        // 末尾のものは新規追加分で置き換わる想定
        array_pop($expectedOprGachaIds);
        array_unshift($expectedOprGachaIds, $newHistoryOprGachaId);

        // 事前にキャッシュにセットする
        $cacheKey = CacheKeyUtil::getGachaHistoryKey($usrUserId);
        $this->setToRedis($cacheKey, $gachaHistories);

        // Exercise
        $gachaHistory = $this->makeGachaHistory($newHistoryOprGachaId, $now);
        $this->service->prependGachaHistory($usrUserId, $gachaHistory);

        // Verify
        $actual = $this->getFromRedis($cacheKey);
        $this->assertCount(GachaConstants::GACHA_HISTORY_LIMIT, $actual);

        // 末尾のものが除外され今回の履歴分が追加されていること
        $actualGachaIds = $actual->map(fn(GachaHistory $history) => $history->formatToResponse()['oprGachaId'])->toArray();
        $this->assertEquals($expectedOprGachaIds, $actualGachaIds);
    }

    private function makeGachaHistory(string $oprGachaId, CarbonImmutable $playedAt): GachaHistory
    {
        return new GachaHistory(
            $oprGachaId,
            'Diamond',
            null,
            300,
            1,
            $playedAt,
            collect(),
        );
    }
}
