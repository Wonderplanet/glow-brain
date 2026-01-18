<?php

namespace Feature\Domain\Message\Eloquent\Models;

use App\Domain\Message\Models\Eloquent\UsrMessage;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class UsrMessageTest extends TestCase
{
    public function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider isExpiredData
     */
    public function isExpired_期限切れチェック(?string $expiredAt, bool $expected): void
    {
        // Setup
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $usrMessage1 = UsrMessage::factory()
            ->set('expired_at', $expiredAt)
            ->createAndConvert();

        // Exercise
        $result = $usrMessage1->isExpired(CarbonImmutable::now());

        // Verify
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array[]
     */
    public static function isExpiredData(): array
    {
        return [
            'expired_atがnull' => [null, false], // 期限なし
            'expired_atが過去' => ['2020-01-14 23:59:59', true], // 期限切れ
            'expired_atが現在時刻と同じ' => ['2020-01-15 00:00:00', false], // 期間内
            'expired_atが未来' => ['2020-01-15 00:00:01', false], // 期間内
        ];
    }
}
