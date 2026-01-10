<?php

namespace Tests\Feature\Domain\Debug\Repositories;

use App\Domain\Debug\Entities\DebugUserTimeSetting;
use App\Domain\Debug\Repositories\DebugUserTimeSettingRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DebugUserTimeSettingRepositoryTest extends TestCase
{
    private DebugUserTimeSettingRepository $debugRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->debugRepository = app(DebugUserTimeSettingRepository::class);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2022-04-07 12:34:56'));
    }

    public function test_put_基本動作確認()
    {
        $userId = $this->createUsrUser()->getId();
        $nowDate = CarbonImmutable::now();
        $targetDate = CarbonImmutable::parse('2030-12-10 11:22:33');
        $this->debugRepository->put($userId, new DebugUserTimeSetting($targetDate));

        // 時刻が固定されているかを確認
        $this->assertEquals($targetDate, CarbonImmutable::now());
        // キャッシュから取得して確認
        $this->assertEquals($targetDate, $this->debugRepository->get($userId)->getUserTime($nowDate));
    }

    public function test_get_基本動作確認()
    {
        $userId = $this->createUsrUser()->getId();
        $targetDate = CarbonImmutable::parse('2030-12-10 12:34:56');
        // キャッシュを追加する(Adminで実装する)
        $setting = new DebugUserTimeSetting(
            $targetDate,
            CarbonImmutable::now()
        );
        Cache::put('debug:UserTime:' . $userId, $setting);

        $debugUserTimeSetting = $this->debugRepository->get($userId);

        $this->assertEquals($targetDate, $debugUserTimeSetting->getUserTime());
    }

    public function test_delete_exist_基本動作確認()
    {
        $userId = $this->createUsrUser()->getId();
        $targetDate = CarbonImmutable::parse('2030-12-10 12:34:56');
        $this->debugRepository->put($userId, new DebugUserTimeSetting($targetDate));

        $this->assertTrue($this->debugRepository->exists($userId));

        $this->debugRepository->delete($userId);

        $this->assertFalse($this->debugRepository->exists($userId));
        $this->assertFalse(CarbonImmutable::hasTestNow());
    }
}
