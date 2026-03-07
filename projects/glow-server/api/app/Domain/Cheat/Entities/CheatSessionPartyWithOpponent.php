<?php

declare(strict_types=1);

namespace App\Domain\Cheat\Entities;

use Illuminate\Support\Collection;

/**
 * 対戦相手がいる場合のチートセッションパーティデータEntity
 * PvP等で自分と対戦相手の両方のパーティデータを保持する
 * usr_cheat_sessions.party_statusの構成を定義
 */
class CheatSessionPartyWithOpponent
{
    /**
     * @param Collection<array> $partyStatuses 自分のパーティステータス
     * @param Collection<array> $artworkPartyStatuses 自分の原画パーティステータス
     * @param Collection<array> $opponentPartyStatuses 対戦相手のパーティステータス
     * @param Collection<array> $opponentArtworkPartyStatuses 対戦相手の原画パーティステータス
     */
    public function __construct(
        private readonly Collection $partyStatuses,
        private readonly Collection $artworkPartyStatuses,
        private readonly Collection $opponentPartyStatuses,
        private readonly Collection $opponentArtworkPartyStatuses,
    ) {
    }

    /**
     * @return Collection<array>
     */
    public function getPartyStatuses(): Collection
    {
        return $this->partyStatuses;
    }

    /**
     * @return Collection<array>
     */
    public function getArtworkPartyStatuses(): Collection
    {
        return $this->artworkPartyStatuses;
    }

    /**
     * @return Collection<array>
     */
    public function getOpponentPartyStatuses(): Collection
    {
        return $this->opponentPartyStatuses;
    }

    /**
     * @return Collection<array>
     */
    public function getOpponentArtworkPartyStatuses(): Collection
    {
        return $this->opponentArtworkPartyStatuses;
    }

    /**
     * 配列からCheatSessionPartyWithOpponentを生成する
     *
     * @param array<mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            collect($data['partyStatuses'] ?? []),
            collect($data['artworkPartyStatuses'] ?? []),
            collect($data['opponentPartyStatuses'] ?? []),
            collect($data['opponentArtworkPartyStatuses'] ?? []),
        );
    }

    /**
     * 配列に変換する
     *
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return [
            'partyStatuses' => $this->partyStatuses->toArray(),
            'artworkPartyStatuses' => $this->artworkPartyStatuses->toArray(),
            'opponentPartyStatuses' => $this->opponentPartyStatuses->toArray(),
            'opponentArtworkPartyStatuses' => $this->opponentArtworkPartyStatuses->toArray(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * チートチェック用に必要な項目のみを抽出したデータを返す
     * 対戦相手の情報は最小限（mst_unit_id、原画ID・グレードのみ）をチェック
     *
     * @return array<mixed>
     */
    public function formatForCheatCheck(): array
    {
        // 自分のパーティデータは全てチェック対象
        $myPartyData = [
            'partyStatuses' => $this->partyStatuses->toArray(),
            'artworkPartyStatuses' => $this->artworkPartyStatuses->toArray(),
        ];

        // 対戦相手のパーティデータは最小限の項目のみチェック
        $opponentPartyStatusSummaries = $this->opponentPartyStatuses->map(function ($status) {
            return [
                'usrUnitId' => $status['usr_unit_id'] ?? $status['usrUnitId'] ?? null,
                'mstUnitId' => $status['mst_unit_id'] ?? $status['mstUnitId'] ?? null,
            ];
        })->toArray();

        $opponentArtworkSummaries = $this->opponentArtworkPartyStatuses->map(function ($status) {
            return [
                'mstArtworkId' => $status['mstArtworkId'] ?? $status['mst_artwork_id'] ?? null,
                'gradeLevel' => $status['gradeLevel'] ?? $status['grade_level'] ?? null,
            ];
        })->toArray();

        return [
            'myParty' => $myPartyData,
            'opponentSummary' => [
                'partyStatusSummaries' => $opponentPartyStatusSummaries,
                'artworkSummaries' => $opponentArtworkSummaries,
            ],
        ];
    }
}
