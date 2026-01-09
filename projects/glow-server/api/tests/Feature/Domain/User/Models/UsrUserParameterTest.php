<?php

namespace Tests\Feature\Domain\User;

use App\Domain\User\Models\UsrUserParameter;
use Tests\TestCase;

class UsrUserParameterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function subtractStamina_スタミナを消費できていることを確認()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'stamina' => 100,
        ]);

        // Exercise
        $usrUserParameter->subtractStamina(10);
        $usrUserParameter->save();

        // Verify
        /** @var UsrUserParameter $usrUserParameter */
        $usrUserParameter = UsrUserParameter::query()->where('id', $usrUserParameter->getId())->first();
        $this->assertEquals($usrUserParameter->getStamina(), 90);
    }

    /**
     * @test
     */
    public function subtractCoin_コインを消費できていることを確認()
    {
        $usrUser = $this->createUsrUser();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'coin' => 100,
        ]);

        $consume = mt_rand(1, $usrUserParameter->getCoin());
        $usrUserParameter->subtractCoin($consume);

        $this->assertEquals(100 - $consume, $usrUserParameter->getCoin());
    }

    // /**
    //  * @test
    //  * @dataProvider params_getDiamond_ダイヤ数を取得できることを確認
    //  */
    // public function getDiamond_ダイヤ数を取得できることを確認(
    //     int $platform,
    //     int $freeDiamond,
    //     int $paidDiamondIos,
    //     int $paidDiamondAndroid,
    //     int $expected
    // ) {
    //     // Setup
    //     $usrUser = $this->createUsrUser();
    //     $usrUserParameter = UsrUserParameter::factory()->create([
    //         'usr_user_id' => $usrUser->getId(),
    //         'free_diamond' => $freeDiamond,
    //         'paid_diamond_ios' => $paidDiamondIos,
    //         'paid_diamond_android' => $paidDiamondAndroid,
    //     ]);

    //     // Exercise
    //     $result = $usrUserParameter->getDiamond($platform);

    //     // Verify
    //     $this->assertEquals($result, $expected);
    // }

    // public function params_getDiamond_ダイヤ数を取得できることを確認()
    // {
    //     return [
    //         'iOS' => [
    //             UserConstant::PLATFORM_IOS,
    //             100,
    //             200,
    //             500,
    //             100 + 200,
    //         ],
    //         'Android' => [
    //             UserConstant::PLATFORM_ANDROID,
    //             100,
    //             200,
    //             500,
    //             100 + 500,
    //         ],
    //     ];
    // }

    // /**
    //  * @test
    //  * @dataProvider params_subtractDiamond_ダイヤを消費できることを確認
    //  */
    // public function subtractDiamond_ダイヤを消費できることを確認(
    //     int $platform,
    //     int $diamondCost,
    //     int $freeDiamond,
    //     int $paidDiamondIos,
    //     int $paidDiamondAndroid,
    //     int $expectedFreeDiamond,
    //     int $expectedPaidDiamond,
    // ) {
    //     // Setup
    //     $usrUser = $this->createUsrUser();
    //     $usrUserParameter = UsrUserParameter::factory()->create([
    //         'usr_user_id' => $usrUser->getId(),
    //         'free_diamond' => $freeDiamond,
    //         'paid_diamond_ios' => $paidDiamondIos,
    //         'paid_diamond_android' => $paidDiamondAndroid,
    //     ]);

    //     // Exercise
    //     $usrUserParameter->subtractDiamond($diamondCost, $platform);
    //     $usrUserParameter->save();

    //     // Verify
    //     /** @var UsrUserParameter $usrUserParameter */
    //     $usrUserParameter = UsrUserParameter::query()->where('id', $usrUserParameter->getId())->first();
    //     $this->assertEquals($usrUserParameter->getFreeDiamond(), $expectedFreeDiamond);
    //     // platformごとに有償ダイヤが減っていることを確認
    //     if ($platform === UserConstant::PLATFORM_IOS) {
    //         $this->assertEquals($usrUserParameter->getPaidDiamondIos(), $expectedPaidDiamond);
    //         $this->assertEquals($usrUserParameter->getPaidDiamondAndroid(), $paidDiamondAndroid);
    //     }
    //     if ($platform === UserConstant::PLATFORM_ANDROID) {
    //         $this->assertEquals($usrUserParameter->getPaidDiamondIos(), $paidDiamondIos);
    //         $this->assertEquals($usrUserParameter->getPaidDiamondAndroid(), $expectedPaidDiamond);
    //     }
    // }

    // public function params_subtractDiamond_ダイヤを消費できることを確認()
    // {
    //     return [
    //         'ios 無償ダイヤを消費' => [UserConstant::PLATFORM_IOS, 100, 101, 0, 0, 1, 0],
    //         'ios 有償ダイヤを消費' => [UserConstant::PLATFORM_IOS, 100, 0, 101, 0, 0, 1],
    //         'ios 無償と有償ダイヤ両方を消費' => [UserConstant::PLATFORM_IOS, 100, 30, 71, 0, 0, 1],

    //         'android 無償ダイヤを消費' => [UserConstant::PLATFORM_ANDROID, 100, 101, 0, 0, 1, 0],
    //         'android 有償ダイヤを消費' => [UserConstant::PLATFORM_ANDROID, 100, 0, 0, 101, 0, 1],
    //         'android 無償と有償ダイヤ両方を消費' => [UserConstant::PLATFORM_ANDROID, 100, 30, 0, 71, 0, 1],

    //         // 異なるplatformの有償ダイヤを消費していないことを確認
    //         'ios androidの有償ダイヤを消費していない' => [UserConstant::PLATFORM_IOS, 100, 20, 200, 300, 0, 120],
    //         'android iosの有償ダイヤを消費していない' => [UserConstant::PLATFORM_ANDROID, 100, 20, 200, 300, 0, 220],
    //     ];
    // }
}
