<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Illuminate\Support\Collection;

interface UsrBoxGachaInterface extends UsrModelInterface
{
    public function getMstBoxGachaId(): string;

    public function getResetCount(): int;

    public function getTotalDrawCount(): int;

    public function getDrawCount(): int;

    public function getCurrentBoxLevel(): int;

    /**
     * 抽選済み賞品を取得（mstBoxGachaPrizeId => count の連想配列）
     *
     * @return Collection<string, int>
     */
    public function getDrawPrizes(): Collection;

    /**
     * 抽選済み賞品を設定
     *
     * @param Collection<string, int> $drawPrizes mstBoxGachaPrizeId => count の連想配列
     */
    public function setDrawPrizes(Collection $drawPrizes): void;

    /**
     * 抽選回数を更新（総抽選回数・現BOX抽選回数の両方）
     */
    public function incrementDrawCounts(int $count): void;

    public function addDrawPrize(string $mstBoxGachaPrizeId, int $count): void;

    public function getDrawnCountByPrizeId(string $mstBoxGachaPrizeId): int;

    /**
     * 現在の箱での抽選済み賞品の総数を取得
     */
    public function getCurrentBoxDrawnCount(): int;

    /**
     * BOXをリセット
     *
     * - リセット回数をインクリメント
     * - 次のBOXレベルに設定
     * - 抽選済み賞品をクリア
     * - 現BOXの抽選回数をクリア
     *
     * @param int $nextBoxLevel リセット後のBOXレベル
     */
    public function reset(int $nextBoxLevel): void;
}
