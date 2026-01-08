<?php

declare(strict_types=1);

namespace App\Domain\Party\Manager\Checker;

use Illuminate\Support\Collection;

interface IRuleChecker
{
    /**
     * ルールに適しているかチェック
     *
     * @param Collection<\App\Domain\Resource\Entities\Unit> $unitEntities
     *
     * @return bool
     */
    public function checkRule(Collection $unitEntities): bool;

    /**
     * ルール情報の文字列取得(※エラーメッセージ用)
     *
     * @return string
     */
    public function getRuleInfo(): string;
}
