<?php

namespace Tests\Feature\Domain\Debug\Repositories;

use App\Domain\Debug\Entities\DebugUserAllTimeSetting;
use App\Domain\Debug\Repositories\DebugUserAllTimeSettingRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DebugUserAllTimeSettingRepositoryTest extends TestCase
{
    private DebugUserAllTimeSettingRepository $debugRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->debugRepository = app(DebugUserAllTimeSettingRepository::class);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2022-04-07 12:34:56'));
    }

    public function test_put_基本動作確認()
    {
        $nowDate = CarbonImmutable::now();
        $targetDate = CarbonImmutable::parse('2030-12-10 11:22:33');
        $this->debugRepository->put(new DebugUserAllTimeSetting($targetDate));

        // 時刻が固定されているかを確認
        $this->assertEquals($targetDate, CarbonImmutable::now());
        // キャッシュから取得して確認
        $this->assertEquals($targetDate, $this->debugRepository->get()->getUserAllTime($nowDate));
    }

    public function test_get_基本動作確認()
    {
        $targetDate = CarbonImmutable::parse('2030-12-10 12:34:56');
        // キャッシュを追加する(Adminで実装する)
        $setting = new DebugUserAllTimeSetting(
            $targetDate,
            CarbonImmutable::now()
        );
        Cache::put('debug:UserAllTime', $setting);

        $debugUserAllTimeSetting = $this->debugRepository->get();

        $this->assertEquals($targetDate, $debugUserAllTimeSetting->getUserAllTime());
    }

    public function test_delete_exist_基本動作確認()
    {
        $targetDate = CarbonImmutable::parse('2030-12-10 12:34:56');
        $this->debugRepository->put(new DebugUserAllTimeSetting($targetDate));

        $this->assertTrue($this->debugRepository->exists());

        $this->debugRepository->delete();

        $this->assertFalse($this->debugRepository->exists());
        $this->assertFalse(CarbonImmutable::hasTestNow());
    }
}
