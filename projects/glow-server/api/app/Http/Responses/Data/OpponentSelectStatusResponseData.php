<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use Illuminate\Support\Collection;

/**
 * レスポンスキー OpponentSelectStatus のデータクラス
 */
class OpponentSelectStatusResponseData
{
    public function __construct(
        private string $myId,
        private string $name,
        private string $mstUnitId,
        private string $mstEmblemId,
        private int $score,
        private OpponentPvpStatusData $opponentPvpStatusData,
        private int $winAddPoint = 0,
    ) {
    }

    public function getMyId(): string
    {
        return $this->myId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMstUnitId(): string
    {
        return $this->mstUnitId;
    }

    public function getMstEmblemId(): string
    {
        return $this->mstEmblemId;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getWinAddPoint(): int
    {
        return $this->winAddPoint;
    }

    public function setWinAddPoint(int $winAddPoint): void
    {
        $this->winAddPoint = $winAddPoint;
    }

    /**
     * フォーマットされたレスポンスデータを返す
     *
     * @return array<string, mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'myId' => $this->getMyId(),
            'name' => $this->getName(),
            'mstUnitId' => $this->getMstUnitId(),
            'mstEmblemId' => $this->getMstEmblemId(),
            'score' => $this->getScore(),
            // partyPvpUnitsは、後方互換(v1.3.1→v1.4.0の時)のために用意しているだけ
            'partyPvpUnits' => $this->opponentPvpStatusData->getPvpUnits()->map(
                function (PvpUnitData $pvpUnitData): array {
                    return $pvpUnitData->formatToResponse();
                }
            )->values()->toArray(),
            'opponentPvpStatus' => $this->opponentPvpStatusData->formatToResponse(),
            'winAddPoint' => $this->getWinAddPoint(),
        ];
    }
}
