<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use App\Domain\Party\Models\UsrArtworkParty;
use App\Domain\Pvp\Enums\PvpMatchingType;
use Illuminate\Support\Collection;

/**
 * レスポンスのデータクラスとしてであれば下記を使う。このクラスの使用は禁止。
 * api/app/Http/Responses/Data/OpponentSelectStatusResponseData.php
 *
 * v1.3.1まではレスポンスとして使っていたが、v1.4.0からは使わない。
 * 対戦相手情報のキャッシュに詰め込むデータクラスとなっているため、変更は避けたい。
 */
class OpponentSelectStatusData
{
    /**
     * @param Collection<PvpUnitData> $partyPvpUnitDatas
     */
    public function __construct(
        private string $myId,
        private string $name,
        private string $mstUnitId,
        private string $mstEmblemId,
        private int $score,
        private Collection $partyPvpUnitDatas,
        private int $winAddPoint = 0,
        private PvpMatchingType $matchingType = PvpMatchingType::None,
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

    public function getPartyPvpUnitDatas(): Collection
    {
        return $this->partyPvpUnitDatas;
    }

    public function getWinAddPoint(): int
    {
        return $this->winAddPoint;
    }

    public function setWinAddPoint(int $winAddPoint): void
    {
        $this->winAddPoint = $winAddPoint;
    }

    public function getMatchingType(): PvpMatchingType
    {
        return $this->matchingType;
    }

    public function setMatchingType(PvpMatchingType $matchingType): void
    {
        $this->matchingType = $matchingType;
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
            'partyPvpUnits' => $this->getPartyPvpUnitDatas()->map(fn(PvpUnitData $unitData) => $unitData->formatToResponse())->values()->toArray(),
            'winAddPoint' => $this->getWinAddPoint(),
        ];
    }

    /**
     * フォーマットされたレスポンスデータを返す
     *
     * @return array<string, mixed>
     */
    public function formatToCacheResponse(): array
    {
        return [
            'myId' => $this->getMyId(),
            'name' => $this->getName(),
            'mstUnitId' => $this->getMstUnitId(),
            'mstEmblemId' => $this->getMstEmblemId(),
            'score' => $this->getScore(),
            'partyPvpUnits' => $this->getPartyPvpUnitDatas()->map(fn(PvpUnitData $unitData) => $unitData->formatToResponse())->values()->toArray(),
            'winAddPoint' => $this->getWinAddPoint(),
            'matchingType' => $this->getMatchingType()->value,
        ];
    }
}
