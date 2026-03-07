<?php

namespace Tests\Feature\Domain\Party\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Party\Constants\PartyConstant;
use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Party\Services\PartyService;
use App\Domain\Resource\Entities\Unit;
use App\Domain\Resource\Mst\Models\MstPartyUnitCount;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PartyServiceTest extends TestCase
{
    private PartyService $partyService;

    public function setUp(): void
    {
        parent::setUp();
        $this->partyService = $this->app->make(PartyService::class);
    }

    public function testSaveParties_パーティが正常に保存できる()
    {
        $usrUser = $this->createUsrUser();
        $usrUnitIds = collect([
            fake()->uuid(),
            fake()->uuid(),
            fake()->uuid(),
            fake()->uuid()
        ]);
        $usrUnitIds->each(function ($usrUnitId) use ($usrUser) {
            return UsrUnit::factory()->create([
                'id' => $usrUnitId,
                'usr_user_id' => $usrUser->getId(),
            ]);
        });
        UsrParty::factory()->createMany([
            [
                'usr_user_id' => $usrUser->getId(),
                'party_no' => 1,
                'party_name' => 'party1',
                'usr_unit_id_1' => $usrUnitIds->get(0),
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'party_no' => 2,
                'party_name' => 'party2',
                'usr_unit_id_1' => $usrUnitIds->get(0),
            ]
        ]);

        $parties = [
            [
                'partyNo' => 1,
                'partyName' => 'party1',
                'units' => [
                    $usrUnitIds->get(0),
                    $usrUnitIds->get(1)
                ]
            ],
            [
                'partyNo' => 2,
                'partyName' => 'party2',
                'units' => [
                    $usrUnitIds->get(2),
                    $usrUnitIds->get(3)
                ]
            ]
        ];
        $this->partyService->saveParties($usrUser->getId(), $parties, $usrUnitIds);
        $this->saveAll();

        $usrParties = UsrParty::query()
            ->where('usr_user_id', $usrUser->getId())
            ->orderBy("party_no")
            ->get();
        foreach ($usrParties as $index => $usrParty) {
            $party = $parties[$index];
            $this->assertEquals($party['partyName'], $usrParty->getPartyName());
            $this->assertEquals($party['units'][0], $usrParty->getUsrUnitId1());
            $this->assertEquals($party['units'][1], $usrParty->getUsrUnitId2());
        }
    }

    public function testSaveParties_パーティのレコードがない場合新規で作成される()
    {
        $usrUser = $this->createUsrUser();
        $usrUnitIds = collect([
            fake()->uuid()
        ]);
        $usrUnitIds->each(function ($usrUnitId) use ($usrUser) {
            return UsrUnit::factory()->create([
                'id' => $usrUnitId,
                'usr_user_id' => $usrUser->getId(),
            ]);
        });



        UsrParty::factory()->createMany([
            [
                'usr_user_id' => $usrUser->getId(),
                'party_no' => 1,
                'party_name' => 'party1',
                'usr_unit_id_1' => $usrUnitIds->get(0),
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'party_no' => 2,
                'party_name' => 'party2',
                'usr_unit_id_1' => $usrUnitIds->get(0),
            ]
        ]);

        $parties = [
            [
                'partyNo' => 1,
                'partyName' => 'party1',
                'units' => [
                    $usrUnitIds->get(0)
                ]
                ],
                [
                    'partyNo' => 2,
                    'partyName' => 'party2',
                    'units' => [
                        $usrUnitIds->get(0)
                    ]
                    ],
                [
                    'partyNo' => 3,
                    'partyName' => 'party3',
                    'units' => [
                        $usrUnitIds->get(0)
                    ]
                    ],
                [
                    'partyNo' => 4,
                    'partyName' => 'party4',
                    'units' => [
                        $usrUnitIds->get(0)
                    ]
                ]
        ];


        $this->partyService->saveParties($usrUser->getId(), $parties, $usrUnitIds);
        $this->saveAll();

        $usrParties = UsrParty::query()
            ->where('usr_user_id', $usrUser->getId())
            ->get();

        $this->assertEquals($usrParties->count(), count($parties));
    }

    public static function params_validatePartyNo_パーティNo検証()
    {
        return [
            '正常1' => ['partyNo' => 1, 'isExceptionThrown' => false],
            '正常2' => ['partyNo' => 10, 'isExceptionThrown' => false],
            '異常1' => ['partyNo' => 0, 'isExceptionThrown' => true],
            '異常2' => ['partyNo' => 11, 'isExceptionThrown' => true],
        ];
    }

    /**
     * @dataProvider params_validatePartyNo_パーティNo検証
     */
    public function testValidatePartyNo_パーティNo検証(int $partyNo, bool $isExceptionThrown)
    {
        if ($isExceptionThrown) {
            // エラーが発生する
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::PARTY_INVALID_PARTY_NO);
        }
        $this->execPrivateMethod($this->partyService, 'validatePartyNo', [$partyNo]);

        // エラーが起きないテストはassertがないのでダミーでassertを入れる
        $this->assertTrue(true);
    }

    public static function params_validatePartyUnits_パーティユニットの検証()
    {
        return [
            '正常1' => [
                'usrUnitIds' => collect(['unit1']),
                'hasUnitIds' => collect(['unit1']),
                'errorCode' => null
            ],
            '正常2' => [
                'usrUnitIds' => collect(['unit1', 'unit2', 'unit3', 'unit4', 'unit5', 'unit6', 'unit7', 'unit8', 'unit9', 'unit10']),
                'hasUnitIds' => collect(['unit1', 'unit2', 'unit3', 'unit4', 'unit5', 'unit6', 'unit7', 'unit8', 'unit9', 'unit10']),
                'errorCode' => null
            ],
            'ユニット数異常1' => [
                'usrUnitIds' => collect(),
                'hasUnitIds' => collect(),
                'errorCode' => ErrorCode::PARTY_INVALID_UNIT_COUNT
            ],
            'ユニット数異常2' => [
                'usrUnitIds' => collect(['unit1', 'unit2', 'unit3', 'unit4', 'unit5', 'unit6', 'unit7', 'unit8', 'unit9', 'unit10', 'unit11']),
                'hasUnitIds' => collect(),
                'errorCode' => ErrorCode::PARTY_INVALID_UNIT_COUNT
            ],
            'ユニットID重複' => [
                'usrUnitIds' => collect(['unit1', 'unit1']),
                'hasUnitIds' => collect(),
                'errorCode' => ErrorCode::PARTY_DUPLICATE_UNIT_ID
            ],
            '不正なユニットID' => [
                'usrUnitIds' => collect(['invalid']),
                'hasUnitIds' => collect(['unit1']),
                'errorCode' => ErrorCode::PARTY_INVALID_UNIT_ID
            ],
        ];
    }

    /**
     * @dataProvider params_validatePartyUnits_パーティユニットの検証
     */
    public function testValidatePartyUnits_パーティユニットの検証(
        Collection $usrUnitIds,
        Collection $hasUnitIds,
        ?int $errorCode
    ) {
        if (!is_null($errorCode)) {
            // エラーが発生する
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }
        $this->execPrivateMethod($this->partyService, 'validatePartyUnits', [$usrUnitIds, $hasUnitIds]);

        // エラーが起きないテストはassertがないのでダミーでassertを入れる
        $this->assertTrue(true);
    }

    public static function params_validatePartyName_パーティ名の検証()
    {
        return [
            '正常1' => ['partyName' => 'あア亜a0!"#$%', 'isExceptionThrown' => false],
            '正常2' => ['partyName' => '&\'()*+,-./', 'isExceptionThrown' => false],
            '正常3' => ['partyName' => ':;<=>?@[]^', 'isExceptionThrown' => false],
            '正常4' => ['partyName' => '_{|}~！＠＃', 'isExceptionThrown' => false],
            '空文字' => ['partyName' => '', 'isExceptionThrown' => true],
            '文字数制限を超える' => ['partyName' => 'abcdefghijk', 'isExceptionThrown' => true],
            '絵文字を含む' => ['partyName' => 'aa🌍bb', 'isExceptionThrown' => true],
            '常用ではない漢字を含む' => ['partyName' => 'aa𠀖bb', 'isExceptionThrown' => true],
            '機種依存文字を含む' => ['partyName' => 'aa㍻bb', 'isExceptionThrown' => true],
            '2byte罫線文字を含む' => ['partyName' => 'aa┌bb', 'isExceptionThrown' => true],
            'ギリシャ文字を含む' => ['partyName' => 'aaΚαληbb', 'isExceptionThrown' => true],
            'ロシア文字を含む' => ['partyName' => 'aaПривbb', 'isExceptionThrown' => true],
            '空白文字を含む' => ['partyName' => 'aa　bb', 'isExceptionThrown' => true],
        ];
    }

    /**
     * @dataProvider params_validatePartyName_パーティ名の検証
     */
    public function testValidatePartyName_パーティ名の検証(string $partyName, bool $isExceptionThrown)
    {
        if ($isExceptionThrown) {
            // エラーが発生する
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::PARTY_INVALID_PARTY_NAME);
        }
        $this->execPrivateMethod($this->partyService, 'validatePartyName', [$partyName]);

        // エラーが起きないテストはassertがないのでダミーでassertを入れる
        $this->assertTrue(true);
    }

    public static function params_test_getParty_パーティ情報を取得できる()
    {
        return [
            '1体のパーティ取得' => [
                'partyNo' => 1,
                'expectedUsrUnitIds' => ['usrUnit1'],
                'expectedMstUnitIds' => ['unit1'],
            ],
            '2体のパーティ取得' => [
                'partyNo' => 2,
                'expectedUsrUnitIds' => ['usrUnit1', 'usrUnit2'],
                'expectedMstUnitIds' => ['unit1', 'unit2'],
            ],
            // 順序がソート順になっていないケース
            '3体のパーティ取得' => [
                'partyNo' => 3,
                'expectedUsrUnitIds' => ['usrUnit3', 'usrUnit1', 'usrUnit2'],
                'expectedMstUnitIds' => ['unit3', 'unit1', 'unit2'],
            ],
        ];
    }

    /**
     * @dataProvider params_test_getParty_パーティ情報を取得できる
     */
    public function test_getParty_パーティ情報を取得できる(
        int $partyNo,
        array $expectedUsrUnitIds,
        array $expectedMstUnitIds,
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        MstUnit::factory()->createMany([
            ['id' => 'unit1'],
            ['id' => 'unit2'],
            ['id' => 'unit3'],
        ]);
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => 1, 'usr_unit_id_1' => 'usrUnit1'],
            ['usr_user_id' => $usrUserId, 'party_no' => 2, 'usr_unit_id_1' => 'usrUnit1', 'usr_unit_id_2' => 'usrUnit2'],
            ['usr_user_id' => $usrUserId, 'party_no' => 3, 'usr_unit_id_1' => 'usrUnit3', 'usr_unit_id_2' => 'usrUnit1', 'usr_unit_id_3' => 'usrUnit2'],
        ]);
        UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2'],
            ['id' => 'usrUnit3', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit3'],
        ]);

        // Exercise
        $result = $this->partyService->getParty($usrUserId, $partyNo);

        // Verify
        $units = $result->getUnits();
        $this->assertInstanceOf(Collection::class, $units);
        $this->assertCount(count($expectedUsrUnitIds), $units);
        $this->assertInstanceOf(Unit::class, $units->first());

        // ユニットの順序も想定通りであることを確認

        $usrUnitIds = $units->map(fn(Unit $unit) => $unit->getUsrUnit()->getUsrUnitId());
        $this->assertEquals($expectedUsrUnitIds, $usrUnitIds->toArray());

        $mstUnitIds = $units->map(fn(Unit $unit) => $unit->getMstUnit()->getId());
        $this->assertEquals($expectedMstUnitIds, $mstUnitIds->toArray());
    }
}
