<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;

/**
 * AdminDebugでコマンドを作成する際に継承するクラス
 * 継承先のクラス名は***UseCaseとして、***の部分がcommandパラメータになる
 */
abstract class BaseCommands
{
    // 継承先でAdminDebugのコマンド名称をオーバーライドする
    protected string $name = '';
    // 継承先でAdminDebugのコマンド内容をオーバーライドする
    protected string $description = '';

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    abstract public function exec(CurrentUser $user, int $platform): void;
}
