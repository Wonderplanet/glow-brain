<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use App\Domain\Pvp\Entities\PvpEncyclopediaEffect;
use App\Domain\Pvp\Enums\PvpMatchingType;
use App\Domain\Resource\Usr\Entities\UsrOutpostEnhancementEntity;
use App\Http\Responses\Data\PvpUnitData;
use Illuminate\Support\Collection;

class OpponentPvpStatusData
{
    /**
     * @param OpponentSelectStatusData $pvpUserProfile
     * @param Collection<PvpUnitData> $pvpUnits
     * @param Collection<UsrOutpostEnhancementEntity> $usrOutpostEnhancements
     * @param Collection<PvpEncyclopediaEffect> $usrEncyclopediaEffects
     * @param Collection<string> $mstArtworkIds
     */
    public function __construct(
        private readonly OpponentSelectStatusData $pvpUserProfile,
        private readonly Collection $pvpUnits,
        private readonly Collection $usrOutpostEnhancements,
        private readonly Collection $usrEncyclopediaEffects,
        private readonly Collection $mstArtworkIds,
    ) {
    }

    public function getPvpUserProfile(): OpponentSelectStatusData
    {
        return $this->pvpUserProfile;
    }

    public function setWinAddPoint(int $winAddPoint): void
    {
        $this->pvpUserProfile->setWinAddPoint($winAddPoint);
    }

    public function setMatchingType(PvpMatchingType $matchingType): void
    {
        $this->pvpUserProfile->setMatchingType($matchingType);
    }

    /**
     * @return Collection<PvpUnitData>
     */
    public function getPvpUnits(): Collection
    {
        return $this->pvpUnits;
    }

    /**
     * @return Collection<UsrOutpostEnhancementEntity>
     */
    public function getUsrOutpostEnhancements(): Collection
    {
        return $this->usrOutpostEnhancements;
    }

    /**
     * @return Collection<PvpEncyclopediaEffect>
     */
    public function getUsrEncyclopediaEffects(): Collection
    {
        return $this->usrEncyclopediaEffects;
    }

    /**
     * @return Collection<string>
     */
    public function getMstArtworkIds(): Collection
    {
        return $this->mstArtworkIds;
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'pvpUnits' => $this->pvpUnits
                ->map(fn(PvpUnitData $unit) => $unit->formatToResponse())
                ->values()->toArray(),
            'usrOutpostEnhancements' => $this->usrOutpostEnhancements
                ->map(fn(UsrOutpostEnhancementEntity $enhancement) => $enhancement->formatToResponse())
                ->values()->toArray(),
            'usrEncyclopediaEffects' => $this->usrEncyclopediaEffects
                ->map(fn(PvpEncyclopediaEffect $effect) => $effect->formatToResponse())
                ->values()->toArray(),
            'mstArtworkIds' => $this->mstArtworkIds->values()->toArray(),
        ];
    }

    public function formatToJson(): string
    {
        $data = [
            'pvpUserProfile' => $this->pvpUserProfile->formatToCacheResponse(),
            'unitStatuses' => $this->pvpUnits
                ->map(fn(PvpUnitData $unit) => $unit->formatToResponse())
                ->values()->toArray(),
            'usrOutpostEnhancements' => $this->usrOutpostEnhancements
                ->map(fn(UsrOutpostEnhancementEntity $enhancement) => $enhancement->formatToLog())
                ->values()->toArray(),
            'usrEncyclopediaEffects' => $this->usrEncyclopediaEffects
                ->map(fn(PvpEncyclopediaEffect $effect) => $effect->formatToResponse())
                ->values()->toArray(),
            'mstArtworkIds' => $this->mstArtworkIds->values()->toArray(),
        ];
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
