<?php

declare(strict_types=1);

namespace App\Domain\Unit\Services;

use App\Domain\Encyclopedia\Delegators\EncyclopediaEffectDelegator;
use App\Domain\Resource\Entities\CheatCheckUnit;
use App\Domain\Resource\Entities\UnitAudit;
use App\Domain\Resource\Mst\Entities\MstUnitEntity;
use App\Domain\Resource\Mst\Entities\MstUnitGradeCoefficientEntity;
use App\Domain\Resource\Mst\Repositories\MstEventBonusUnitRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitGradeCoefficientRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitLevelUpRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitRankCoefficientRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitRankUpRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitSpecificRankUpRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Unit\Constants\UnitConstant;
use Illuminate\Support\Collection;

/**
 * ユニットのステータス計算を行うサービス
 * ※クライアント側を参考にしているため、クライアント側の計算ロジックが変わったら修正する必要性があります
 */
class UnitStatusService
{
    public function __construct(
        // Services
        private readonly MstConfigService $mstConfigService,
        // Repositories
        private readonly MstUnitLevelUpRepository $mstUnitLevelUpRepository,
        private readonly MstUnitRankCoefficientRepository $mstUnitRankCoefficientRepository,
        private readonly MstUnitGradeCoefficientRepository $mstUnitGradeCoefficientRepository,
        private readonly MstEventBonusUnitRepository $mstEventBonusUnitRepository,
        private readonly MstUnitRepository $mstUnitRepository,
        private readonly MstUnitRankUpRepository $mstUnitRankUpRepository,
        private readonly MstUnitSpecificRankUpRepository $mstUnitSpecificRankUpRepository,
        // Delegators
        private readonly EncyclopediaEffectDelegator $encyclopediaEffectDelegator,
    ) {
    }

    /**
     * ユニットデータをユニットステータスに変換する
     *
     * @param MstUnitEntity  $mstUnit
     * @param CheatCheckUnit $cheatCheckUnit
     * @param int            $maxLevel
     * @param int|null       $rankRequireLevel
     * @param float          $statusExponent
     * @param int            $rankCoefficient
     * @param int            $gradeCoefficient
     * @return UnitAudit
     */
    public function convertUnitDataToUnitStatusData(
        MstUnitEntity $mstUnit,
        CheatCheckUnit $cheatCheckUnit,
        int $maxLevel,
        ?int $rankRequireLevel,
        float $statusExponent,
        int $rankCoefficient,
        int $gradeCoefficient,
    ): UnitAudit {

        $baseHp = $this->calculateStatus(
            $cheatCheckUnit->getLevel(),
            $rankRequireLevel,
            $mstUnit->getMinHp(),
            $mstUnit->getMaxHp(),
            $maxLevel,
            $statusExponent,
            $rankCoefficient,
            $gradeCoefficient,
        );

        $baseAtk = $this->calculateStatus(
            $cheatCheckUnit->getLevel(),
            $rankRequireLevel,
            $mstUnit->getMinAttackPower(),
            $mstUnit->getMaxAttackPower(),
            $maxLevel,
            $statusExponent,
            $rankCoefficient,
            $gradeCoefficient,
        );

        return new UnitAudit($mstUnit, $cheatCheckUnit, (int) $baseHp, (int) $baseAtk);
    }

    /**
     * ステータスを計算する関数
     *
     * @param int      $unitLevel
     * @param int|null $rankRequireLevel
     * @param int      $minStatus
     * @param int      $maxStatus
     * @param int      $maxLevel
     * @param float    $statusExponent
     * @param int      $rankCoefficient
     * @param int      $gradeCoefficient
     * @return float
     */
    private function calculateStatus(
        int $unitLevel,
        ?int $rankRequireLevel,
        int $minStatus,
        int $maxStatus,
        int $maxLevel,
        float $statusExponent,
        int $rankCoefficient,
        int $gradeCoefficient,
    ): float {
        // 基本ステータスの計算
        $baseStatus = $this->calculateBaseStatus($minStatus, $maxStatus, $unitLevel, $maxLevel, $statusExponent);


        // ランクボーナスステータスの初期化
        $rankBonusStatus = 0.0;
        if ($rankCoefficient !== 0 && $rankRequireLevel !== null) {
            $rankReference = $this->calculateBaseStatus(
                $minStatus,
                $maxStatus,
                $rankRequireLevel,
                $maxLevel,
                $statusExponent
            );
            $rankBonusStatus = $this->calculateRankStatus($rankReference, $rankCoefficient);
        }

        // グレードボーナスステータスの初期化
        $gradeBonusStatus = 0.0;
        if ($gradeCoefficient > 0) {
            // グレード補正を適用
            $gradeBonusStatus = $this->calculateGradeStatus($baseStatus, $gradeCoefficient);
        }

        // 総ステータス計算
        $finalStatus = $baseStatus + $rankBonusStatus + $gradeBonusStatus;

        // 総ステータスを返す (基本ステータス + ランクボーナス + グレードボーナス)
        return $finalStatus;
    }

    /**
     * 基本ステータスを計算する
     *
     * @param int $minStatus
     * @param int $maxStatus
     * @param int $currentLevel
     * @param int $maxLevel
     * @param float $statusExponent
     * @return float
     */
    private function calculateBaseStatus(
        int $minStatus,
        int $maxStatus,
        int $currentLevel,
        int $maxLevel,
        float $statusExponent,
    ): float {
        // 基本ステータス: Lv1時の値+(最大Lv時の値-Lv1時の値)*((現在Lv-1)/(最大Lv-1))
        $v = pow(($currentLevel - 1) / ($maxLevel - 1), $statusExponent);
        return floor($minStatus + ($maxStatus - $minStatus) * $v);
    }

    /**
     * ランク補正ステータスを計算する
     *
     * @param float $rankReferenceStatus
     * @param int $rankCoefficient
     * @return float
     */
    private function calculateRankStatus(float $rankReferenceStatus, int $rankCoefficient): float
    {
        // (ランク参照ステータス * 補正係数 * 0.01) を計算
        return floor($rankReferenceStatus * $rankCoefficient * 0.01);
    }

    /**
     * グレード補正ステータスを計算する
     *
     * @param float $baseStatus
     * @param int $gradeCoefficient
     * @return float
     */
    private function calculateGradeStatus(float $baseStatus, int $gradeCoefficient): float
    {
        // 基本ステータスにグレード補正を適用
        return floor($baseStatus * $gradeCoefficient * 0.01);
    }

    /**
     * ユニットデータのリストをユニットステータスのリストに変換する
     *
     * @param Collection<CheatCheckUnit> $units
     * @return Collection<UnitAudit>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function convertUnitDataListToUnitStatusDataList(Collection $units): Collection
    {
        $mstUnitIds = $units->map(fn(CheatCheckUnit $unit) => $unit->getMstUnitId());
        $mstUnits = $this->mstUnitRepository->getByIds($mstUnitIds);
        $mstUnitIdLabelPairs = $mstUnits->mapWithKeys(
            fn(MstUnitEntity $mstUnit) => [$mstUnit->getId() => $mstUnit->getUnitLabel()]
        );
        $ranks = $units->map(fn(CheatCheckUnit $unit) => $unit->getRank());
        $unitLabelRankPairs = $units->map(fn(CheatCheckUnit $unit) => [
            $mstUnits->get($unit->getMstUnitId())->getUnitLabel(), $unit->getRank(),
        ]);
        $unitLabelGradeLevelPairs = $units->map(fn(CheatCheckUnit $unit) => [
            $mstUnits->get($unit->getMstUnitId())->getUnitLabel(), $unit->getGradeLevel(),
        ]);

        $mstUnitLevelUps = $this->mstUnitLevelUpRepository->getMaxMstUnitLevelUps(
            $mstUnitIdLabelPairs->values(),
            true,
        );
        $statusExponent = $this->mstConfigService->getUnitStatusExponent();
        $rankCoefficients = $this->mstUnitRankCoefficientRepository->getCoefficientsByRanks(
            $ranks
        );
        $mstUnitGradeCoefficients = $this->mstUnitGradeCoefficientRepository->getByUnitLabelsAndGradeLevels(
            $unitLabelGradeLevelPairs,
            true,
        )->keyBy(function (MstUnitGradeCoefficientEntity $entity) {
            return $entity->makeUnitLabelAndGradeLevelKey();
        });

        $mstUnitIdRankMap = $units->mapWithKeys(
            fn(CheatCheckUnit $unit) => [$unit->getMstUnitId() => $unit->getRank()]
        );
        $mstUnitSpecificRankUps = $this->mstUnitSpecificRankUpRepository->getByMstUnitIdRankMap($mstUnitIdRankMap);
        $mstUnitRankUps = $this->mstUnitRankUpRepository->getByUnitLabelsAndRanks($unitLabelRankPairs);

        return $units->map(function (CheatCheckUnit $unit) use (
            $mstUnitLevelUps,
            $statusExponent,
            $rankCoefficients,
            $mstUnitGradeCoefficients,
            $mstUnits,
            $mstUnitSpecificRankUps,
            $mstUnitRankUps,
        ): UnitAudit {
            $mstUnit = $mstUnits->get($unit->getMstUnitId());
            $mstUnitLevelUp = $mstUnitLevelUps->get($mstUnit->getUnitLabel());
            $rankCoefficient = $rankCoefficients->get($unit->getRank());
            $gradeCoefficient = $mstUnitGradeCoefficients->get(
                MstUnitGradeCoefficientEntity::makeUnitLabelAndGradeLevelKeyStatic(
                    $mstUnit->getUnitLabel(),
                    $unit->getGradeLevel()
                )
            )?->getCoefficient() ?? UnitConstant::DEFAULT_UNIT_GRADE_COEFFICIENT;
            $mstUnitRankUp = $mstUnitSpecificRankUps->get($unit->getMstUnitId())?->get($unit->getRank());
            if ($mstUnitRankUp === null) {
                $mstUnitRankUp = $mstUnitRankUps->get($mstUnit->getUnitLabel())?->get($unit->getRank());
            }
            return $this->convertUnitDataToUnitStatusData(
                $mstUnit,
                $unit,
                $mstUnitLevelUp->getLevel(),
                $mstUnitRankUp?->getRequireLevel() ?? null,
                $statusExponent,
                $rankCoefficient,
                $gradeCoefficient
            );
        });
    }

    /**
     * ユニットステータスのリストにパラメータ効果やボーナスを付加する
     *
     * @param Collection<UnitAudit> $unitAudits
     * @param ?string $eventBonusGroupId
     * @param Collection $mstUnitEncyclopediaEffectIds
     * @return void
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function assignEffectBonusesToUnitStatus(
        Collection $unitAudits,
        ?string $eventBonusGroupId,
        Collection $mstUnitEncyclopediaEffectIds,
    ): void {
        $encyclopediaEffectData = $this->encyclopediaEffectDelegator->getEncyclopediaEffectDataByIds(
            $mstUnitEncyclopediaEffectIds
        );

        $eventBonuses = collect();
        if (!is_null($eventBonusGroupId)) {
            $eventBonuses = $this->mstEventBonusUnitRepository->getByEventBonusGroupIdAndMstUnitIds(
                $eventBonusGroupId,
                $unitAudits->map(
                    fn(UnitAudit $unitAudit) => $unitAudit->getCheatCheckUnit()->getMstUnitId()
                )
            )->keyBy(function ($eventBonus): string {
                return $eventBonus->getMstUnitId();
            });
        }

        $unitAudits->each(function (UnitAudit $unitAudit) use ($encyclopediaEffectData, $eventBonuses) {
            // 図鑑効果を付与
            $unitAudit->setUnitEncyclopediaEffectBonusHpPercentage(
                $encyclopediaEffectData->getHpEffectPercentage()
            );
            $unitAudit->setUnitEncyclopediaEffectBonusAtkPercentage(
                $encyclopediaEffectData->getAttackPowerEffectPercentage()
            );

            // イベントボーナスを付与
            $eventBonus = $eventBonuses->get($unitAudit->getCheatCheckUnit()->getMstUnitId());
            $unitAudit->setEventBonusPercentage($eventBonus?->getBonusPercentage() ?? 0);
        });
    }
}
