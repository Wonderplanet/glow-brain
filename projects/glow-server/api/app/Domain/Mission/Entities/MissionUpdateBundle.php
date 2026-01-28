<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities;

use App\Domain\Mission\Entities\Criteria\MissionCriterion;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\IUsrMission;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionDependencyEntityInterface;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityInterface;
use Illuminate\Support\Collection;

class MissionUpdateBundle
{
    /** @var Collection<MissionState> */
    private Collection $states;

    /** @var Collection<MissionChain> */
    private Collection $chains;

    /**
     * @param Collection<string, covariant MstMissionEntityInterface> $mstMissions トリガーされて進捗変動があるミッションのマスタ
     *  key: mst_mission_id
     * @param null|Collection<covariant MstMissionDependencyEntityInterface> $mstMissionDependencies
     *  トリガーされて進捗変動があるミッションが属するミッション依存関係グループのマスタ
     * @param Collection<string, MissionCriterion> $criteria key: criterion_key 追加進捗を集約済のMissionCriterion配列
     * @param null|Collection<string, IUsrMission> $usrMissions ユーザーの進捗データ
     */
    public function __construct(
        private MissionType $missionType,
        private Collection $mstMissions,
        private null|Collection $mstMissionDependencies,
        private Collection $criteria,
        private ?Collection $usrMissions = null,
    ) {
        $this->states = collect();
    }

    public function getMissionType(): MissionType
    {
        return $this->missionType;
    }

    /**
     * @return Collection<string, covariant MstMissionEntityInterface>
     */
    public function getMstMissions(): Collection
    {
        return $this->mstMissions;
    }

    public function getMstMissionIds(): Collection
    {
        return $this->getMstMissions()->keys();
    }

    public function getMstMissionDependencies(): Collection
    {
        if ($this->hasDependency() === false) {
            return collect();
        }
        return $this->mstMissionDependencies;
    }

    public function hasDependency(): bool
    {
        return $this->mstMissionDependencies instanceof Collection && !$this->mstMissionDependencies->isEmpty();
    }

    /**
     * @return Collection<string, MissionCriterion> key: criterion_key
     */
    public function getCriteria(): Collection
    {
        return $this->criteria;
    }

    public function getUsrMissions(): Collection
    {
        return $this->usrMissions;
    }

    public function setUsrMissions(Collection $usrMissions): void
    {
        $this->usrMissions = $usrMissions;
    }

    public function getStates(): Collection
    {
        return $this->states;
    }

    public function setStates(Collection $states): void
    {
        $this->states = $states;
    }

    public function getChains(): Collection
    {
        return $this->chains;
    }

    public function setChains(Collection $chains): void
    {
        $this->chains = $chains;
    }
}
