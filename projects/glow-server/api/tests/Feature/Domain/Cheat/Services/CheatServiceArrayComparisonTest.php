<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Cheat\Services;

use App\Domain\Cheat\Services\CheatService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CheatServiceArrayComparisonTest extends TestCase
{
    private CheatService $cheatService;
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();

        // CheatServiceのモックを作成（依存関係は不要なのでモック）
        $this->cheatService = $this->getMockBuilder(CheatService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reflection = new ReflectionClass(CheatService::class);
    }

    /**
     * arraysAreEqualRecursiveWithToleranceメソッドを呼び出すヘルパー
     */
    private function callArraysAreEqualRecursiveWithTolerance(
        array $array1,
        array $array2,
        array $numericFields = ['atk', 'hp'],
        int $allowedDifference = 5
    ): bool {
        $method = $this->reflection->getMethod('arraysAreEqualRecursiveWithTolerance');
        $method->setAccessible(true);

        return $method->invokeArgs($this->cheatService, [
            $array1,
            $array2,
            $numericFields,
            $allowedDifference
        ]);
    }

    public function test_arraysAreEqualRecursiveWithTolerance_完全一致の場合はtrueを返す(): void
    {
        $array1 = ['atk' => 1000, 'hp' => 2000, 'name' => 'test'];
        $array2 = ['atk' => 1000, 'hp' => 2000, 'name' => 'test'];

        $result = $this->callArraysAreEqualRecursiveWithTolerance($array1, $array2);

        $this->assertTrue($result);
    }

    public function test_arraysAreEqualRecursiveWithTolerance_数値フィールドで許容範囲内の差分がある場合はtrueを返す(): void
    {
        $array1 = ['atk' => 1000, 'hp' => 2000];
        $array2 = ['atk' => 1005, 'hp' => 2005]; // 5以下の差分

        $result = $this->callArraysAreEqualRecursiveWithTolerance($array1, $array2);

        $this->assertTrue($result);
    }

    public function test_arraysAreEqualRecursiveWithTolerance_数値フィールドで許容範囲ぎりぎりの差分がある場合はtrueを返す(): void
    {
        $array1 = ['atk' => 1000, 'hp' => 2000];
        $array2 = ['atk' => 995, 'hp' => 1995]; // ちょうど5の差分

        $result = $this->callArraysAreEqualRecursiveWithTolerance($array1, $array2);

        $this->assertTrue($result);
    }

    public function test_arraysAreEqualRecursiveWithTolerance_数値フィールドで許容範囲を超える差分がある場合はfalseを返す(): void
    {
        $array1 = ['atk' => 1000, 'hp' => 2000];
        $array2 = ['atk' => 1006, 'hp' => 2000]; // 6の差分（許容範囲5を超える）

        $result = $this->callArraysAreEqualRecursiveWithTolerance($array1, $array2);

        $this->assertFalse($result);
    }

    public function test_arraysAreEqualRecursiveWithTolerance_非数値フィールドは完全一致で比較される(): void
    {
        $array1 = ['name' => 'test', 'atk' => 1000];
        $array2 = ['name' => 'test2', 'atk' => 1000]; // nameフィールドが異なる

        $result = $this->callArraysAreEqualRecursiveWithTolerance($array1, $array2);

        $this->assertFalse($result);
    }

    public function test_arraysAreEqualRecursiveWithTolerance_ネストした配列でも数値フィールドの許容誤差が適用される(): void
    {
        $array1 = [
            'units' => [
                ['atk' => 1000, 'hp' => 2000, 'name' => 'unit1'],
                ['atk' => 1500, 'hp' => 2500, 'name' => 'unit2']
            ]
        ];
        $array2 = [
            'units' => [
                ['atk' => 1005, 'hp' => 2005, 'name' => 'unit1'], // 5以下の差分
                ['atk' => 1500, 'hp' => 2500, 'name' => 'unit2']
            ]
        ];

        $result = $this->callArraysAreEqualRecursiveWithTolerance($array1, $array2);

        $this->assertTrue($result);
    }

    public function test_arraysAreEqualRecursiveWithTolerance_ネストした配列で許容範囲を超える場合はfalseを返す(): void
    {
        $array1 = [
            'units' => [
                ['atk' => 1000, 'hp' => 2000],
                ['atk' => 1500, 'hp' => 2500]
            ]
        ];
        $array2 = [
            'units' => [
                ['atk' => 1000, 'hp' => 2000],
                ['atk' => 1507, 'hp' => 2500] // 7の差分（許容範囲5を超える）
            ]
        ];

        $result = $this->callArraysAreEqualRecursiveWithTolerance($array1, $array2);

        $this->assertFalse($result);
    }

    public function test_arraysAreEqualRecursiveWithTolerance_カスタム数値フィールドと許容誤差を指定できる(): void
    {
        $array1 = ['power' => 1000, 'defense' => 500, 'name' => 'test'];
        $array2 = ['power' => 1010, 'defense' => 510, 'name' => 'test']; // 10の差分

        // powerとdefenseフィールドで許容誤差15を指定
        $result = $this->callArraysAreEqualRecursiveWithTolerance(
            $array1,
            $array2,
            ['power', 'defense'],
            15
        );

        $this->assertTrue($result);
    }

    public function test_arraysAreEqualRecursiveWithTolerance_配列の要素数が異なる場合はfalseを返す(): void
    {
        $array1 = ['atk' => 1000, 'hp' => 2000];
        $array2 = ['atk' => 1000]; // 要素数が異なる

        $result = $this->callArraysAreEqualRecursiveWithTolerance($array1, $array2);

        $this->assertFalse($result);
    }

    public function test_arraysAreEqualRecursiveWithTolerance_キーが存在しない場合はfalseを返す(): void
    {
        $array1 = ['atk' => 1000, 'hp' => 2000];
        $array2 = ['atk' => 1000, 'mp' => 2000]; // hpキーが存在しない

        $result = $this->callArraysAreEqualRecursiveWithTolerance($array1, $array2);

        $this->assertFalse($result);
    }

    public function test_arraysAreEqualRecursiveWithTolerance_数値以外の値が数値フィールドにある場合は完全一致で比較される(): void
    {
        $array1 = ['atk' => 'high', 'hp' => 2000];
        $array2 = ['atk' => 'high', 'hp' => 2005];

        $result = $this->callArraysAreEqualRecursiveWithTolerance($array1, $array2);

        $this->assertTrue($result); // atkは文字列なので完全一致、hpは許容範囲内
    }

    public function test_arraysAreEqualRecursiveWithTolerance_負の数値でも許容誤差が適用される(): void
    {
        $array1 = ['atk' => -1000, 'hp' => -500];
        $array2 = ['atk' => -995, 'hp' => -505]; // 5の差分

        $result = $this->callArraysAreEqualRecursiveWithTolerance($array1, $array2);

        $this->assertTrue($result);
    }

    public function test_arraysAreEqualRecursiveWithTolerance_新形式パーティデータが一致する場合はtrueを返す(): void
    {
        $array1 = [
            'partyStatuses' => [
                ['usrUnitId' => 'u1', 'mstUnitId' => 'm1', 'atk' => 100, 'hp' => 200],
                ['usrUnitId' => 'u2', 'mstUnitId' => 'm2', 'atk' => 150, 'hp' => 250],
            ],
            'artworkPartyStatuses' => [
                ['mstArtworkId' => 'artwork_1', 'gradeLevel' => 1],
                ['mstArtworkId' => 'artwork_2', 'gradeLevel' => 1],
            ],
        ];
        $array2 = [
            'partyStatuses' => [
                ['usrUnitId' => 'u1', 'mstUnitId' => 'm1', 'atk' => 100, 'hp' => 200],
                ['usrUnitId' => 'u2', 'mstUnitId' => 'm2', 'atk' => 150, 'hp' => 250],
            ],
            'artworkPartyStatuses' => [
                ['mstArtworkId' => 'artwork_1', 'gradeLevel' => 1],
                ['mstArtworkId' => 'artwork_2', 'gradeLevel' => 1],
            ],
        ];

        $result = $this->callArraysAreEqualRecursiveWithTolerance($array1, $array2);

        $this->assertTrue($result);
    }

    public function test_arraysAreEqualRecursiveWithTolerance_新形式パーティデータで原画IDが異なる場合はfalseを返す(): void
    {
        $array1 = [
            'partyStatuses' => [
                ['usrUnitId' => 'u1', 'mstUnitId' => 'm1', 'atk' => 100, 'hp' => 200],
            ],
            'artworkPartyStatuses' => [
                ['mstArtworkId' => 'artwork_1', 'gradeLevel' => 1],
                ['mstArtworkId' => 'artwork_2', 'gradeLevel' => 1],
            ],
        ];
        $array2 = [
            'partyStatuses' => [
                ['usrUnitId' => 'u1', 'mstUnitId' => 'm1', 'atk' => 100, 'hp' => 200],
            ],
            'artworkPartyStatuses' => [
                ['mstArtworkId' => 'artwork_changed', 'gradeLevel' => 1],
                ['mstArtworkId' => 'artwork_2', 'gradeLevel' => 1],
            ],
        ];

        $result = $this->callArraysAreEqualRecursiveWithTolerance($array1, $array2);

        $this->assertFalse($result);
    }

    public function test_arraysAreEqualRecursiveWithTolerance_新形式パーティデータでステータス許容誤差内かつ原画一致はtrueを返す(): void
    {
        $array1 = [
            'partyStatuses' => [
                ['usrUnitId' => 'u1', 'mstUnitId' => 'm1', 'atk' => 100, 'hp' => 200],
            ],
            'artworkPartyStatuses' => [
                ['mstArtworkId' => 'artwork_1', 'gradeLevel' => 1],
            ],
        ];
        $array2 = [
            'partyStatuses' => [
                ['usrUnitId' => 'u1', 'mstUnitId' => 'm1', 'atk' => 105, 'hp' => 205],
            ],
            'artworkPartyStatuses' => [
                ['mstArtworkId' => 'artwork_1', 'gradeLevel' => 1],
            ],
        ];

        $result = $this->callArraysAreEqualRecursiveWithTolerance($array1, $array2);

        $this->assertTrue($result);
    }

    public function test_arraysAreEqualRecursiveWithTolerance_新形式パーティデータで原画ID数が異なる場合はfalseを返す(): void
    {
        $array1 = [
            'partyStatuses' => [
                ['usrUnitId' => 'u1', 'mstUnitId' => 'm1', 'atk' => 100, 'hp' => 200],
            ],
            'artworkPartyStatuses' => [
                ['mstArtworkId' => 'artwork_1', 'gradeLevel' => 1],
                ['mstArtworkId' => 'artwork_2', 'gradeLevel' => 1],
            ],
        ];
        $array2 = [
            'partyStatuses' => [
                ['usrUnitId' => 'u1', 'mstUnitId' => 'm1', 'atk' => 100, 'hp' => 200],
            ],
            'artworkPartyStatuses' => [
                ['mstArtworkId' => 'artwork_1', 'gradeLevel' => 1],
            ],
        ];

        $result = $this->callArraysAreEqualRecursiveWithTolerance($array1, $array2);

        $this->assertFalse($result);
    }
}
