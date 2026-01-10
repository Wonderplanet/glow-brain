<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Traits;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Mission\Constants\MissionConstant;
use App\Domain\Mission\Enums\MissionCriterionConditionType;
use App\Domain\Resource\Mst\Repositories\Contracts\IMissionCriterion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait MstMissionRepositoryTrait
{
    /**
     * criterion_key(criterion_typeとcriterion_valueの組み合わせ)でデータ取得するようにクエリを作成する。
     * 存在しないcriterion_typeなどでクエリを作らないために、MissionCriterionを引数としています。
     *
     * @param Builder $query
     * @param Collection<IMissionCriterion> $criteria
     * @param Collection<string> $mstEventIds
     * @return Builder
     */
    protected function makeQueryByCriteriaAndMstEventIds(
        Builder $query,
        Collection $criteria,
        Collection $mstEventIds,
    ): Builder {
        if ($criteria->isEmpty()) {
            return $query;
        }

        $query->where(function ($query) use ($criteria) {
            foreach ($criteria as $criterion) {
                /** @var IMissionCriterion $criterion */

                // 達成条件
                $this->addClearWhereByCriterion($query, $criterion);

                // 開放条件
                $this->addUnlockWhereByCriterion($query, $criterion);
            }
            // 複合ミッション条件
            $this->addCompositeMissionWhere($query);
        });
        // イベントID条件
        $this->addMstEventIdWhere($query, $mstEventIds);

        return $query;
    }

    /**
     * 達成条件によるクエリ追加
     */
    protected function addClearWhereByCriterion(
        Builder $query,
        IMissionCriterion $criterion,
    ): Builder {
        $criterionType = $criterion->getType();
        $criterionValue = $criterion->getValue();

        $criterionConditionTypes = $criterion->getConditionTypes();

        if (
            !in_array(
                MissionCriterionConditionType::CLEAR,
                $criterionConditionTypes,
            )
        ) {
            return $query;
        }

        $query->orWhere(function ($query) use ($criterionType, $criterionValue) {
            $query->where('criterion_type', $criterionType);
            if (StringUtil::isSpecified($criterionValue)) {
                $query->where('criterion_value', $criterionValue);
            }
        });

        return $query;
    }

    /**
     * 開放条件によるクエリ追加
     */
    protected function addUnlockWhereByCriterion(
        Builder $query,
        IMissionCriterion $criterion,
    ): Builder {
        $criterionType = $criterion->getType();
        $criterionValue = $criterion->getValue();

        $criterionConditionTypes = $criterion->getConditionTypes();

        if (
            !in_array(
                MissionCriterionConditionType::UNLOCK,
                $criterionConditionTypes,
            )
        ) {
            return $query;
        }

        $query->orWhere(function ($query) use ($criterionType, $criterionValue) {
            $query->where('unlock_criterion_type', $criterionType);
            if (StringUtil::isSpecified($criterionValue)) {
                $query->where('unlock_criterion_value', $criterionValue);
            }
        });

        return $query;
    }

    /**
     * 複合ミッション取得のクエリ追加
     */
    protected function addCompositeMissionWhere(Builder $query): Builder
    {
        return $query->orWhereIn('criterion_type', MissionConstant::COMPOSITE_MISSION_CRITERION_TYPES);
    }

    /**
     * イベントID絞り込みのクエリ追加
     */
    protected function addMstEventIdWhere(Builder $query, Collection $mstEventIds): Builder
    {
        return $query->whereIn('mst_event_id', $mstEventIds);
    }
}
