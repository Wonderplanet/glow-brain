<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Common\Utils\StringUtil;

class MstArtworkPanelMissionEntity
{
    public function __construct(
        private string $id,
        private string $mstArtworkId,
        private string $mstEventId,
        private ?string $initialOpenMstArtworkFragmentId,
        private string $startAt,
        private string $endAt,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstArtworkId(): string
    {
        return $this->mstArtworkId;
    }

    public function getMstEventId(): string
    {
        return $this->mstEventId;
    }

    public function getInitialOpenMstArtworkFragmentId(): ?string
    {
        return $this->initialOpenMstArtworkFragmentId;
    }

    public function getStartAt(): string
    {
        return $this->startAt;
    }

    public function getEndAt(): string
    {
        return $this->endAt;
    }

    /**
     * 初期開放する原画のかけらがあるかどうか
     * @return bool true: 初期開放するかけらがある, false: ない
     */
    public function hasInitialOpenArtworkFragment(): bool
    {
        return StringUtil::isSpecified($this->initialOpenMstArtworkFragmentId);
    }
}
