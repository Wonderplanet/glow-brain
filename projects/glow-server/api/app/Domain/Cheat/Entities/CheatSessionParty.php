<?php

declare(strict_types=1);

namespace App\Domain\Cheat\Entities;

use Illuminate\Support\Collection;

/**
 * チートセッションのパーティデータEntity
 * usr_cheat_sessions.party_statusの構成を定義
 */
class CheatSessionParty
{
    /**
     * @param Collection<array> $partyStatuses
     * @param Collection<array> $artworkPartyStatuses
     */
    public function __construct(
        private readonly Collection $partyStatuses,
        private readonly Collection $artworkPartyStatuses,
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
     * 配列からCheatSessionPartyを生成する
     *
     * @param array<mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        // 新形式: 'partyStatuses'と'artworkPartyStatuses'キーが両方存在する
        if (isset($data['partyStatuses'], $data['artworkPartyStatuses'])) {
            return new self(
                collect($data['partyStatuses']),
                collect($data['artworkPartyStatuses']),
            );
        }

        // 旧形式: フラット配列を新形式に変換
        // artworkPartyStatuses を空配列にすることで旧形式との互換性を保つ
        return new self(
            collect($data),
            collect([]),
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
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
