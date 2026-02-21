<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\DebugCommand\Exceptions\InvalidDebugCommandParameterException;
use App\Domain\DebugCommand\UseCases\Commands\BaseParameterizedCommands;
use LogicException;
use Tests\TestCase;

/**
 * テスト用のconcrete実装
 */
class TestableParameterizedCommand extends BaseParameterizedCommands
{
    public bool $executed = false;

    /** @var array<string, mixed> */
    public array $executedParams = [];

    protected string $name = 'テスト用コマンド';
    protected string $description = 'テスト用の説明';

    /** @var array<string, array<string, mixed>> */
    protected array $requiredParameters = [
        'count' => [
            'type' => 'integer',
            'min' => 1,
            'max' => 100,
            'description' => 'テスト用カウント',
        ],
    ];

    protected function doExecWithParams(CurrentUser $user, int $platform, array $params): void
    {
        $this->executed = true;
        $this->executedParams = $params;
    }
}

/**
 * カスタムバリデーション付きのテスト用concrete実装
 */
class TestableCustomValidationCommand extends BaseParameterizedCommands
{
    protected string $name = 'カスタムバリデーションテスト';
    protected string $description = 'カスタムバリデーションのテスト用';

    /** @var array<string, array<string, mixed>> */
    protected array $requiredParameters = [
        'start' => [
            'type' => 'integer',
            'min' => 1,
            'max' => 100,
            'description' => '開始値',
        ],
        'end' => [
            'type' => 'integer',
            'min' => 1,
            'max' => 100,
            'description' => '終了値',
        ],
    ];

    protected function validateParametersCustom(array $params): void
    {
        if ($params['end'] <= $params['start']) {
            throw new InvalidDebugCommandParameterException(
                'endはstartより大きい値を指定してください'
            );
        }
    }

    protected function doExecWithParams(CurrentUser $user, int $platform, array $params): void
    {
        // no-op
    }
}

class BaseParameterizedCommandsValidationTest extends TestCase
{
    private TestableParameterizedCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->command = new TestableParameterizedCommand();
    }

    public function test_必須パラメータが未設定の場合にExceptionが発生する(): void
    {
        $this->expectException(InvalidDebugCommandParameterException::class);
        $this->expectExceptionMessage('count(テスト用カウント)パラメータは必須です');

        $user = new \Tests\Support\Entities\CurrentUser(fake()->uuid());
        $this->command->execWithParams($user, 1, []);
    }

    public function test_integer型に文字列を渡すとExceptionが発生する(): void
    {
        $this->expectException(InvalidDebugCommandParameterException::class);
        $this->expectExceptionMessage('countパラメータは整数型で指定してください');

        $user = new \Tests\Support\Entities\CurrentUser(fake()->uuid());
        $this->command->execWithParams($user, 1, ['count' => 'abc']);
    }

    public function test_integer型にfloatを渡すとExceptionが発生する(): void
    {
        $this->expectException(InvalidDebugCommandParameterException::class);
        $this->expectExceptionMessage('countパラメータは整数型で指定してください');

        $user = new \Tests\Support\Entities\CurrentUser(fake()->uuid());
        $this->command->execWithParams($user, 1, ['count' => 1.5]);
    }

    public function test_範囲下限を下回るとExceptionが発生する(): void
    {
        $this->expectException(InvalidDebugCommandParameterException::class);
        $this->expectExceptionMessage('countパラメータは1以上で指定してください');

        $user = new \Tests\Support\Entities\CurrentUser(fake()->uuid());
        $this->command->execWithParams($user, 1, ['count' => 0]);
    }

    public function test_範囲上限を超えるとExceptionが発生する(): void
    {
        $this->expectException(InvalidDebugCommandParameterException::class);
        $this->expectExceptionMessage('countパラメータは100以下で指定してください');

        $user = new \Tests\Support\Entities\CurrentUser(fake()->uuid());
        $this->command->execWithParams($user, 1, ['count' => 101]);
    }

    public function test_正常値の場合はバリデーションを通過しビジネスロジックが実行される(): void
    {
        $user = new \Tests\Support\Entities\CurrentUser(fake()->uuid());
        $this->command->execWithParams($user, 1, ['count' => 50]);

        $this->assertTrue($this->command->executed);
        $this->assertSame(['count' => 50], $this->command->executedParams);
    }

    public function test_範囲の境界値minで正常に通過する(): void
    {
        $user = new \Tests\Support\Entities\CurrentUser(fake()->uuid());
        $this->command->execWithParams($user, 1, ['count' => 1]);

        $this->assertTrue($this->command->executed);
    }

    public function test_範囲の境界値maxで正常に通過する(): void
    {
        $user = new \Tests\Support\Entities\CurrentUser(fake()->uuid());
        $this->command->execWithParams($user, 1, ['count' => 100]);

        $this->assertTrue($this->command->executed);
    }

    public function test_カスタムバリデーションが標準バリデーション後に実行される(): void
    {
        $command = new TestableCustomValidationCommand();
        $user = new \Tests\Support\Entities\CurrentUser(fake()->uuid());

        $this->expectException(InvalidDebugCommandParameterException::class);
        $this->expectExceptionMessage('endはstartより大きい値を指定してください');

        $command->execWithParams($user, 1, ['start' => 50, 'end' => 50]);
    }

    public function test_カスタムバリデーションが正常に通過する(): void
    {
        $command = new TestableCustomValidationCommand();
        $user = new \Tests\Support\Entities\CurrentUser(fake()->uuid());

        // Exceptionが投げられなければ成功
        $command->execWithParams($user, 1, ['start' => 10, 'end' => 50]);
        $this->assertTrue(true);
    }

    public function test_未対応の型を指定するとLogicExceptionが発生する(): void
    {
        $command = new class extends BaseParameterizedCommands {
            protected string $name = 'test';
            protected string $description = 'test';
            /** @var array<string, array<string, mixed>> */
            protected array $requiredParameters = [
                'param' => [
                    'type' => 'unknown_type',
                    'description' => 'テスト',
                ],
            ];

            protected function doExecWithParams(CurrentUser $user, int $platform, array $params): void
            {
            }
        };

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('未対応のパラメータ型: unknown_type');

        $user = new \Tests\Support\Entities\CurrentUser(fake()->uuid());
        $command->execWithParams($user, 1, ['param' => 'value']);
    }

    public function test_不正なパラメータの場合ビジネスロジックは実行されない(): void
    {
        $user = new \Tests\Support\Entities\CurrentUser(fake()->uuid());

        try {
            $this->command->execWithParams($user, 1, ['count' => 'invalid']);
        } catch (InvalidDebugCommandParameterException) {
            // expected
        }

        $this->assertFalse($this->command->executed);
    }
}
