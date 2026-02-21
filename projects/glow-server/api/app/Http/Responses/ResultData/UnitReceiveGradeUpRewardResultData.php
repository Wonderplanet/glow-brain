<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Encyclopedia\Models\UsrArtworkFragmentInterface;
use App\Domain\Encyclopedia\Models\UsrArtworkInterface;
use App\Domain\Unit\Models\UsrUnitInterface;
use Illuminate\Support\Collection;

/**
 * ユニットグレードアップ報酬受け取りのレスポンスデータ
 */
class UnitReceiveGradeUpRewardResultData
{
    /**
     * @param UsrUnitInterface $usrUnit
     * @param Collection<UsrArtworkInterface> $usrArtworks
     * @param Collection<UsrArtworkFragmentInterface> $usrArtworkFragments
     * @param Collection<\App\Domain\Resource\Entities\Rewards\UnitGradeUpReward> $unitGradeUpRewards
     */
    public function __construct(
        public readonly UsrUnitInterface $usrUnit,
        public readonly Collection $usrArtworks,
        public readonly Collection $usrArtworkFragments,
        public readonly Collection $unitGradeUpRewards,
    ) {
    }
}
