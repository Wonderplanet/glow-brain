<?php

declare(strict_types=1);

namespace App\Domain\Stage\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

/**
 * クエストタイプごとのステージ進捗データを管理するユーザーテーブルで必ず実装させるインターフェース
 * 全クエストタイプで共通の処理を記述する際に使っています
 */
interface IBaseUsrStage extends UsrModelInterface
{
    public function getMstStageId(): string;

    public function isClear(): bool;

    public function getClearCount(): int;

    public function incrementClearCount(): void;

    public function addClearCount(int $addNum): void;

    public function isFirstClear(): bool;
}
