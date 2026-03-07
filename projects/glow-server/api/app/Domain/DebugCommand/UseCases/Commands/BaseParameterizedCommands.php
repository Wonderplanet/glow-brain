<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\DebugCommand\Exceptions\InvalidDebugCommandParameterException;
use LogicException;

/**
 * パラメータ付きデバッグコマンドの基底クラス
 *
 * パラメータを受け取るデバッグコマンドを作成する際に継承する
 * 継承先のクラス名は***UseCaseとして、***の部分がcommandパラメータになる
 *
 * requiredParametersを定義するだけでバリデーションが自動実行される
 * サブクラスはdoExecWithParams()にビジネスロジックを実装する
 */
abstract class BaseParameterizedCommands extends BaseCommands
{
    /**
     * パラメータ付きコマンドであることを示すフラグ
     */
    protected bool $isParameterized = true;

    /**
     * 必須パラメータの定義
     * キー: パラメータ名
     * 値: パラメータの詳細情報（type, min, max, description等）
     * @var array<string, array<string, mixed>>
     */
    protected array $requiredParameters = [];

    /**
     * パラメータ付きコマンドかどうかを判定
     */
    public function isParameterized(): bool
    {
        return $this->isParameterized;
    }

    /**
     * 必須パラメータの定義を取得
     * @return array<string, array<string, mixed>>
     */
    public function getRequiredParameters(): array
    {
        return $this->requiredParameters;
    }

    /**
     * パラメータ付きコマンドの実行（Template Method）
     * バリデーション実行後、サブクラスのビジネスロジックを呼び出す
     * @param CurrentUser $user
     * @param int $platform
     * @param array<string, mixed> $params
     */
    final public function execWithParams(CurrentUser $user, int $platform, array $params): void
    {
        $this->validateParameters($params);
        $this->doExecWithParams($user, $platform, $params);
    }

    /**
     * パラメータ付きコマンドのビジネスロジック
     * 継承先で実装する
     * @param CurrentUser $user
     * @param int $platform
     * @param array<string, mixed> $params
     */
    abstract protected function doExecWithParams(CurrentUser $user, int $platform, array $params): void;

    /**
     * requiredParametersの定義に基づくパラメータの動的バリデーション
     * @param array<string, mixed> $params
     * @throws InvalidDebugCommandParameterException
     */
    final protected function validateParameters(array $params): void
    {
        foreach ($this->requiredParameters as $paramName => $definition) {
            $this->validateRequired($paramName, $params);

            $type = $definition['type'] ?? null;
            if ($type !== null) {
                match ($type) {
                    'integer' => $this->validateIntegerParam($paramName, $params[$paramName], $definition),
                    default => throw new LogicException("未対応のパラメータ型: {$type}"),
                };
            }
        }

        $this->validateParametersCustom($params);
    }

    /**
     * カスタムバリデーション用hookメソッド
     * 標準バリデーション通過後に呼ばれる
     * クロスパラメータ検証等が必要な場合にサブクラスでoverrideする
     * @param array<string, mixed> $params
     * @throws InvalidDebugCommandParameterException
     */
    protected function validateParametersCustom(array $params): void
    {
        // デフォルトはno-op
    }

    /**
     * 必須パラメータの存在チェック
     * @param string $paramName
     * @param array<string, mixed> $params
     * @throws InvalidDebugCommandParameterException
     */
    private function validateRequired(string $paramName, array $params): void
    {
        if (!array_key_exists($paramName, $params)) {
            $description = $this->requiredParameters[$paramName]['description'] ?? $paramName;
            throw new InvalidDebugCommandParameterException(
                "{$paramName}({$description})パラメータは必須です"
            );
        }
    }

    /**
     * integer型パラメータの検証（型チェック + min/max範囲チェック）
     * @param string $paramName
     * @param mixed $value
     * @param array<string, mixed> $definition
     * @throws InvalidDebugCommandParameterException
     */
    private function validateIntegerParam(string $paramName, mixed $value, array $definition): void
    {
        if (!is_int($value)) {
            throw new InvalidDebugCommandParameterException(
                "{$paramName}パラメータは整数型で指定してください"
            );
        }

        $min = $definition['min'] ?? null;
        if ($min !== null && $value < $min) {
            throw new InvalidDebugCommandParameterException(
                "{$paramName}パラメータは{$min}以上で指定してください"
            );
        }

        $max = $definition['max'] ?? null;
        if ($max !== null && $value > $max) {
            throw new InvalidDebugCommandParameterException(
                "{$paramName}パラメータは{$max}以下で指定してください"
            );
        }
    }

    /**
     * 従来型のexec()メソッドは使用不可
     * パラメータ付きコマンドはexecWithParams()を使用すること
     * @param CurrentUser $user
     * @param int $platform
     * @throws LogicException
     */
    final public function exec(CurrentUser $user, int $platform): void
    {
        throw new LogicException(
            'パラメータ付きコマンドではexec()は使用できません。execWithParams()を使用してください。'
        );
    }
}
