<?php

declare(strict_types=1);

namespace App\Domain\Mission;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Enums\MissionType;
use Illuminate\Support\Collection;

class MissionManager
{
    /**
     * @var Collection<string, Collection<MissionTrigger>> 処理前のトリガーデータの配列
     *
     * Collection<missionType, Collection<MissionTrigger>>
     */
    private Collection $triggers;

    public function __construct()
    {
        $this->triggers = collect();
    }

    /**
     * Triggers
     */

    /**
     * 重複してミッション判定をしないために、トリガーデータを取得した後に、triggersを初期化する
     *
     * @return Collection<MissionTrigger>
     */
    public function popTriggers(MissionType $missionType): Collection
    {
        $targetMissionType = $missionType->value;

        $triggers = $this->triggers->get($targetMissionType, collect());

        $this->triggers->put($targetMissionType, collect());

        return $triggers;
    }

    public function addTrigger(MissionTrigger $trigger, ?MissionType $missionType = null): void
    {
        if ($trigger->isValid() === false) {
            return;
        }

        $targetMissionTypes = collect(MissionType::cases());

        // missionTypeに指定があれば、指定されたmissionTypeの連想配列にのみ追加
        if ($missionType !== null) {
            $targetMissionTypes = collect([$missionType]);
        }

        foreach ($targetMissionTypes as $missionType) {
            /** @var MissionType $missionType */
            $targetMissionType = $missionType->value;

            $this->triggers->put(
                $targetMissionType,
                $this->triggers
                    ->get($targetMissionType, collect())
                    ->push($trigger)
            );
        }
    }

    public function addTriggers(Collection $triggers, ?MissionType $missionType = null): void
    {
        $triggers->each(
            fn(MissionTrigger $trigger) => $this->addTrigger($trigger, $missionType)
        );
    }
}
