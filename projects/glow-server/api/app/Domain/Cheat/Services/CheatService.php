<?php

declare(strict_types=1);

namespace App\Domain\Cheat\Services;

use App\Domain\Cheat\Constants\CheatConstant;
use App\Domain\Cheat\Enums\CheatType;
use App\Domain\Cheat\Repositories\LogSuspectedUserRepository;
use App\Domain\Cheat\Repositories\UsrCheatSessionRepository;
use App\Domain\Encyclopedia\Delegators\EncyclopediaEffectDelegator;
use App\Domain\Party\Delegators\PartyDelegator;
use App\Domain\Resource\Entities\CheatCheckUnit;
use App\Domain\Resource\Entities\PartyStatus;
use App\Domain\Resource\Entities\Unit;
use App\Domain\Resource\Entities\UnitAudit;
use App\Domain\Resource\Mst\Entities\MstAttackEntity;
use App\Domain\Resource\Mst\Entities\MstCheatSettingEntity;
use App\Domain\Resource\Mst\Entities\MstUnitEncyclopediaEffectEntity;
use App\Domain\Resource\Mst\Repositories\MstAttackRepository;
use App\Domain\Resource\Mst\Repositories\MstCheatSettingRepository;
use App\Domain\Unit\Delegators\UnitDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class CheatService
{
    /**
     * ステータス不一致の許容範囲
     * 例えば、攻撃力が1000のユニットに対して、995～1005の範囲であれば不一致としない
     */
    private const ALLOWED_STATUS_DIFFERENCE = 5;

    public function __construct(
        // Repositories
        private readonly MstCheatSettingRepository $mstCheatSettingRepository,
        private readonly LogSuspectedUserRepository $logSuspectedUserRepository,
        private readonly UsrCheatSessionRepository $usrCheatSessionRepository,
        private readonly MstAttackRepository $mstAttackRepository,
        // Delegators
        private readonly PartyDelegator $partyDelegator,
        private readonly UnitDelegator $unitDelegator,
        private readonly EncyclopediaEffectDelegator $encyclopediaEffectDelegator,
    ) {
    }

    /**
     * チート判定を行う
     *
     * @param string $usrUserId
     * @param string $targetId
     * @param string $cheatContentType
     * @param string $cheatType
     * @param CarbonImmutable $now
     * @param callable $callback
     * @return MstCheatSettingEntity|null
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    private function executeCheck(
        string $usrUserId,
        string $targetId,
        string $cheatContentType,
        string $cheatType,
        CarbonImmutable $now,
        callable $callback
    ): ?MstCheatSettingEntity {
        $mstCheatSettings = $this->mstCheatSettingRepository->getByType($cheatContentType, $cheatType, $now);
        if ($mstCheatSettings->isEmpty()) {
            return null; // チート設定がない場合はチート判定を行わない
        }

        $result = $callback($mstCheatSettings->first());
        if (!is_null($result)) {
            $this->logSuspectedUserRepository->create(
                $usrUserId,
                $cheatContentType,
                $targetId,
                $cheatType,
                $result,
                $now,
            );
            return $mstCheatSettings->first();
        }

        return null;
    }

    /**
     * バトル時間(秒数)によるチート判定を行う
     *
     * @param string $usrUserId
     * @param string $targetId 降臨バトルやステージのID
     * @param string $cheatContentType
     * @param CarbonImmutable $now
     * @param int $battleTimeSeconds
     * @return MstCheatSettingEntity|null チート検出されなかった場合は通常プレイヤーと判断してnullを返す
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function checkBattleTime(
        string $usrUserId,
        string $targetId,
        string $cheatContentType,
        CarbonImmutable $now,
        int $battleTimeSeconds,
    ): ?MstCheatSettingEntity {
        return $this->executeCheck(
            $usrUserId,
            $targetId,
            $cheatContentType,
            CheatType::BATTLE_TIME->value,
            $now,
            function (MstCheatSettingEntity $mstCheatSetting) use ($battleTimeSeconds) {
                if ($battleTimeSeconds > $mstCheatSetting->getCheatValue()) {
                    return null;
                }

                return [
                    CheatConstant::LOG_DETAIL_KEY_BATTLE_TIME_SECONDS => $battleTimeSeconds,
                ];
            }
        );
    }

    /**
     * 一発の最大ダメージ量によるチート判定を行う
     *
     * @param string $usrUserId
     * @param string $targetId 降臨バトルやステージのID
     * @param string $cheatContentType
     * @param CarbonImmutable $now
     * @param int $maxDamage
     * @return MstCheatSettingEntity|null チート検出されなかった場合は通常プレイヤーと判断してnullを返す
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function checkMaxDamage(
        string $usrUserId,
        string $targetId,
        string $cheatContentType,
        CarbonImmutable $now,
        int $maxDamage,
    ): ?MstCheatSettingEntity {
        return $this->executeCheck(
            $usrUserId,
            $targetId,
            $cheatContentType,
            CheatType::MAX_DAMAGE->value,
            $now,
            function (MstCheatSettingEntity $mstCheatSetting) use ($maxDamage) {
                if ($maxDamage < $mstCheatSetting->getCheatValue()) {
                    return null;
                }

                return [
                    CheatConstant::LOG_DETAIL_KEY_MAX_DAMAGE => $maxDamage,
                ];
            }
        );
    }

    /**
     * バトル前後のパーティステータス不一致によるチート判定の準備を行う
     *
     * @param string $usrUserId
     * @param string $targetId
     * @param string $cheatContentType
     * @param Collection<\App\Domain\Resource\Entities\PartyStatus> $partyStatuses
     * @return void
     */
    public function initBattleStatusMismatch(
        string $usrUserId,
        string $targetId,
        string $cheatContentType,
        Collection $partyStatuses,
    ): void {
        $usrCheatSession = $this->usrCheatSessionRepository->findOrCreate($usrUserId);
        $partyStatusesArray = $partyStatuses->map(function ($partyStatus) {
            return $partyStatus->formatToLog();
        })->toArray();
        $usrCheatSession->setPartyStatus($cheatContentType, $targetId, $partyStatusesArray);
        $this->usrCheatSessionRepository->syncModel($usrCheatSession);
    }

    /**
     * バトル前後のパーティステータス不一致によるチート判定を行う
     *
     * @param string $usrUserId
     * @param string $targetId 降臨バトルやステージのID
     * @param string $cheatContentType
     * @param CarbonImmutable $now
     * @param Collection<\App\Domain\Resource\Entities\PartyStatus> $partyStatuses
     * @return MstCheatSettingEntity|null チート検出されなかった場合は通常プレイヤーと判断してnullを返す
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function checkBattleStatusMismatch(
        string $usrUserId,
        string $targetId,
        string $cheatContentType,
        CarbonImmutable $now,
        Collection $partyStatuses,
    ): ?MstCheatSettingEntity {
        return $this->executeCheck(
            $usrUserId,
            $targetId,
            $cheatContentType,
            CheatType::BATTLE_STATUS_MISMATCH->value,
            $now,
            function (MstCheatSettingEntity $mstCheatSetting) use (
                $usrUserId,
                $targetId,
                $cheatContentType,
                $partyStatuses,
            ) {
                $usrCheatSession = $this->usrCheatSessionRepository->findOrCreate($usrUserId);
                $partyStatusesArray = $partyStatuses->map(function ($partyStatus) {
                    return $partyStatus->formatToLog();
                })->toArray();
                $beforeBattlePartyStatusesArray = json_decode($usrCheatSession->getPartyStatus(), true);

                if (
                    $usrCheatSession->getContentType() === $cheatContentType &&
                    $usrCheatSession->getTargetId() === $targetId &&
                    $this->arraysAreEqualRecursiveWithTolerance(
                        $beforeBattlePartyStatusesArray,
                        $partyStatusesArray
                    )
                ) {
                    return null;
                }

                return [
                    CheatConstant::LOG_DETAIL_KEY_BEFORE_BATTLE_PARTY_STATUSES => $beforeBattlePartyStatusesArray,
                    CheatConstant::LOG_DETAIL_KEY_PARTY_STATUSES => $partyStatusesArray,
                ];
            }
        );
    }

    /**
     * マスターデータ不一致によるチート判定を行う
     *
     * @param string $usrUserId
     * @param string $targetId 降臨バトルやステージのID
     * @param string $cheatContentType
     * @param CarbonImmutable $now
     * @param Collection<\App\Domain\Resource\Entities\PartyStatus> $partyStatuses
     * @param int $partyNo
     * @param string $eventBonusGroupId
     * @return MstCheatSettingEntity|null チート検出されなかった場合は通常プレイヤーと判断してnullを返す
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function checkMasterDataStatusMismatch(
        string $usrUserId,
        string $targetId,
        string $cheatContentType,
        CarbonImmutable $now,
        Collection $partyStatuses,
        int $partyNo,
        string $eventBonusGroupId,
    ): ?MstCheatSettingEntity {
        return $this->executeCheck(
            $usrUserId,
            $targetId,
            $cheatContentType,
            CheatType::MASTER_DATA_STATUS_MISMATCH->value,
            $now,
            function (MstCheatSettingEntity $mstCheatSetting) use (
                $partyStatuses,
                $partyNo,
                $usrUserId,
                $eventBonusGroupId,
            ) {
                $party = $this->partyDelegator->getParty($usrUserId, $partyNo);
                $units = $party->getUnits()->map(fn(Unit $unit) => $unit->toCheatCheckUnit());
                $unitAudits = $this->unitDelegator
                    ->convertUnitDataListToUnitStatusDataList($units)
                    ->keyBy(function (UnitAudit $unitAuditEntity) {
                        return $unitAuditEntity->getCheatCheckUnit()->getMstUnitId();
                    });

                // 発動中のキャラ図鑑ランク効果を取得
                $unitGradeLevelTotalCount = $this->unitDelegator->getGradeLevelTotalCount($usrUserId);
                $mstUnitEncyclopediaEffectIds = $this->encyclopediaEffectDelegator
                    ->getMstUnitEncyclopediaEffectsByGrade($unitGradeLevelTotalCount)
                    ->map(function (MstUnitEncyclopediaEffectEntity $entity) {
                        return $entity->getId();
                    });

                // 各種ボーナスをUnitAuditに反映
                $this->unitDelegator->assignEffectBonusesToUnitStatus(
                    $unitAudits,
                    $eventBonusGroupId,
                    $mstUnitEncyclopediaEffectIds,
                );

                // チェック
                $msg = collect();
                $partyStatusUsrUnitIds = $partyStatuses->map(function ($partyStatus) {
                    return $partyStatus->getUsrUnitId();
                })->unique();

                if ($partyStatusUsrUnitIds->count() !== $unitAudits->count()) {
                    $msg->push(
                        'Mismatch: partyStatuses count (' . $partyStatusUsrUnitIds->count() . ')'
                        . 'is not equal to unitAuditList count (' . $unitAudits->count() . ')'
                    );
                } else {
                    $mstUnitIdGradeMap = $unitAudits->mapWithKeys(
                        fn(UnitAudit $unitAudit) => [
                            $unitAudit->getMstUnit()->getId() => $unitAudit->getCheatCheckUnit()->getGradeLevel(),
                        ]
                    );
                    $normalAttackMstAttacks = $this->mstAttackRepository->getNormalAttacks(
                        $unitAudits->map(
                            fn(UnitAudit $unitAuditEntity) => $unitAuditEntity->getMstUnit()->getId()
                        )
                    );
                    $specialAttackMstAttacks = $this->mstAttackRepository->getSpecialAttacks(
                        $mstUnitIdGradeMap
                    );
                    foreach ($partyStatuses as $partyStatus) {
                        $unitAuditEntity = $unitAudits->get($partyStatus->getMstUnitId());
                        if (!$unitAuditEntity) {
                            $msg->push(sprintf(
                                'Mismatch: mstUnitId=%s not found in unitDataList',
                                $partyStatus->getMstUnitId()
                            ));
                            continue;
                        }

                        $mstUnitId = $unitAuditEntity->getMstUnit()->getId();
                        $mismatchMessages = $this->checkStatusMismatch(
                            $partyStatus,
                            $unitAuditEntity,
                            $specialAttackMstAttacks->get($mstUnitId)
                                ->get($unitAuditEntity->getCheatCheckUnit()->getGradeLevel()),
                            $normalAttackMstAttacks->get($mstUnitId)
                        );
                        if ($mismatchMessages->isNotEmpty()) {
                            $msg->push(sprintf(
                                'Mismatch: mstUnitId=%s (%s)',
                                $partyStatus->getMstUnitId(),
                                $mismatchMessages->implode(' | ')
                            ));
                        }
                    }
                }
                if ($msg->isEmpty()) {
                    return null;
                }

                $unitAuditArray = $unitAudits->map(function ($unitAuditEntity) {
                    return $unitAuditEntity->formatToLog();
                })->toArray();
                $partyStatusesArray = $partyStatuses->map(function ($partyStatus) {
                    return $partyStatus->formatToLog();
                })->toArray();

                return [
                    CheatConstant::LOG_DETAIL_KEY_MSG => $msg->toArray(),
                    CheatConstant::LOG_DETAIL_KEY_UNIT_DATA => $unitAuditArray,
                    CheatConstant::LOG_DETAIL_KEY_PARTY_STATUSES => $partyStatusesArray,
                ];
            }
        );
    }

    /**
     * ここがユニットのステータスチェックをしている。
     * opponentPvpStatusDataから計算できるステータスと実際のステータスが違う場合
     * マスターデータ不一致によるチート判定を行う
     *
     * @param string $usrUserId
     * @param string $targetId 降臨バトルやステージのID
     * @param string $cheatContentType
     * @param CarbonImmutable $now
     * @param Collection<\App\Domain\Resource\Entities\PartyStatus> $partyStatuses
     * @param Collection<CheatCheckUnit> $cheatCheckUnits
     * @return MstCheatSettingEntity|null チート検出されなかった場合は通常プレイヤーと判断してnullを返す
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function checkOpponentMasterDataStatusMismatch(
        string $usrUserId,
        string $targetId,
        string $cheatContentType,
        CarbonImmutable $now,
        Collection $partyStatuses,
        Collection $cheatCheckUnits,
        Collection $mstUnitEncyclopediaEffectIds,
    ): ?MstCheatSettingEntity {
        return $this->executeCheck(
            $usrUserId,
            $targetId,
            $cheatContentType,
            CheatType::MASTER_DATA_STATUS_MISMATCH->value,
            $now,
            function (MstCheatSettingEntity $mstCheatSetting) use (
                $partyStatuses,
                $cheatCheckUnits,
                $mstUnitEncyclopediaEffectIds,
            ) {
                $unitAudits = $this->unitDelegator
                    ->convertUnitDataListToUnitStatusDataList($cheatCheckUnits)
                    ->keyBy(function (UnitAudit $unitAuditEntity) {
                        return $unitAuditEntity->getCheatCheckUnit()->getMstUnitId();
                    });
                $this->unitDelegator->assignEffectBonusesToUnitStatus(
                    $unitAudits,
                    null, // ランクマッチにイベントボーナスはない
                    $mstUnitEncyclopediaEffectIds
                );

                $msg = collect();
                $partyStatusUsrUnitIds = $partyStatuses->map(function ($partyStatus) {
                    return $partyStatus->getMstUnitId();
                })->unique();

                if ($partyStatusUsrUnitIds->count() !== $cheatCheckUnits->count()) {
                    $msg->push(
                        'Mismatch: partyStatuses count (' . $partyStatusUsrUnitIds->count() . ')'
                        . 'is not equal to unitAuditList count (' . $cheatCheckUnits->count() . ')'
                    );
                } else {
                    $mstUnitIdGradeMap = $unitAudits->mapWithKeys(
                        fn(UnitAudit $unitAudit) => [
                            $unitAudit->getMstUnit()->getId() => $unitAudit->getCheatCheckUnit()->getGradeLevel(),
                        ]
                    );

                    $normalAttackMstAttacks = $this->mstAttackRepository->getNormalAttacks(
                        $unitAudits->map(
                            fn(UnitAudit $unitAuditEntity) => $unitAuditEntity->getMstUnit()->getId()
                        )
                    );
                    $specialAttackMstAttacks = $this->mstAttackRepository->getSpecialAttacks(
                        $mstUnitIdGradeMap
                    );
                    foreach ($partyStatuses as $partyStatus) {
                        $unitAuditEntity = $unitAudits->get($partyStatus->getMstUnitId());
                        if (!$unitAuditEntity) {
                            $msg->push(sprintf(
                                'Mismatch: mstUnitId=%s not found in unitDataList',
                                $partyStatus->getMstUnitId()
                            ));
                            continue;
                        }

                        $mstUnitId = $unitAuditEntity->getMstUnit()->getId();
                        $mismatchMessages = $this->checkStatusMismatch(
                            $partyStatus,
                            $unitAuditEntity,
                            $specialAttackMstAttacks->get($mstUnitId)
                                ->get($unitAuditEntity->getCheatCheckUnit()->getGradeLevel()),
                            $normalAttackMstAttacks->get($mstUnitId)
                        );
                        if ($mismatchMessages->isNotEmpty()) {
                            $msg->push(sprintf(
                                'Mismatch: mstUnitId=%s (%s)',
                                $partyStatus->getMstUnitId(),
                                $mismatchMessages->implode(' | ')
                            ));
                        }
                    }
                }
                if ($msg->isEmpty()) {
                    return null;
                }

                $unitAuditArray = $unitAudits->map(function ($unitAuditEntity) {
                    return $unitAuditEntity->formatToLog();
                })->toArray();
                $partyStatusesArray = $partyStatuses->map(function ($partyStatus) {
                    return $partyStatus->formatToLog();
                })->toArray();

                return [
                    CheatConstant::LOG_DETAIL_KEY_MSG => $msg->toArray(),
                    CheatConstant::LOG_DETAIL_KEY_UNIT_DATA => $unitAuditArray,
                    CheatConstant::LOG_DETAIL_KEY_PARTY_STATUSES => $partyStatusesArray,
                ];
            }
        );
    }

    /**
     * ステータスの差分が許容範囲内かどうかを判定する
     *
     * @param int $status1
     * @param int $status2
     * @return bool
     */
    private function isWithinAllowedDifference(int $status1, int $status2): bool
    {
        return abs($status1 - $status2) <= self::ALLOWED_STATUS_DIFFERENCE;
    }

    /**
     * 再帰的に2つの配列が等しいかどうかを比較
     * 数値フィールド（atk, hp）に対しては許容誤差を適用する
     *
     * @param array<mixed> $array1
     * @param array<mixed> $array2
     * @param array<string> $numericFields 許容誤差を適用する数値フィールド名の配列
     * @param int $allowedDifference 数値フィールドの許容誤差
     * @return bool
     */
    private function arraysAreEqualRecursiveWithTolerance(
        array $array1,
        array $array2,
        array $numericFields = ['atk', 'hp'],
        int $allowedDifference = self::ALLOWED_STATUS_DIFFERENCE
    ): bool {
        ksort($array1);
        ksort($array2);
        if ($array1 === $array2) {
            return true;
        }
        if (count($array1) !== count($array2)) {
            return false;
        }

        // 配列を再帰的に比較
        foreach ($array1 as $key => $value) {
            if (!array_key_exists($key, $array2)) {
                return false;
            }

            if (is_array($value) && is_array($array2[$key])) {
                if (
                    !$this->arraysAreEqualRecursiveWithTolerance(
                        $value,
                        $array2[$key],
                        $numericFields,
                        $allowedDifference
                    )
                ) {
                    return false;
                }
            } elseif (in_array($key, $numericFields) && is_numeric($value) && is_numeric($array2[$key])) {
                // 数値フィールドで許容誤差内かどうかをチェック
                if (abs((int)$value - (int)$array2[$key]) > $allowedDifference) {
                    return false;
                }
            } elseif ($value !== $array2[$key]) {
                return false;
            }
        }

        return true;
    }

    /**
     * パーティステータス不一致のチート判定を行う
     *
     * @param PartyStatus      $partyStatus
     * @param UnitAudit        $unitAuditEntity
     * @param ?MstAttackEntity $mstAttackSpecial
     * @param ?MstAttackEntity $mstAttackNormal
     * @return Collection
     */
    private function checkStatusMismatch(
        PartyStatus $partyStatus,
        UnitAudit $unitAuditEntity,
        ?MstAttackEntity $mstAttackSpecial = null,
        ?MstAttackEntity $mstAttackNormal = null
    ): Collection {
        $msg = collect();
        $mstUnit = $unitAuditEntity->getMstUnit();
        $format = '[%s](expected: %s, actual: %s)';

        if ($partyStatus->getMstUnitId() !== $mstUnit->getId()) {
            $msg->push(sprintf(
                $format,
                'mstUnitId',
                $mstUnit->getId(),
                $partyStatus->getMstUnitId()
            ));
        }
        if (!$this->isWithinAllowedDifference($partyStatus->getHp(), $unitAuditEntity->getBoostedHp())) {
            $msg->push(sprintf(
                $format,
                'hp',
                $unitAuditEntity->getBoostedHp(),
                $partyStatus->getHp()
            ));
        }
        if (!$this->isWithinAllowedDifference($partyStatus->getAtk(), $unitAuditEntity->getBoostedAtk())) {
            $msg->push(sprintf(
                $format,
                'atk',
                $unitAuditEntity->getBoostedAtk(),
                $partyStatus->getAtk()
            ));
        }

        if (!is_null($mstAttackSpecial)) {
            if ($partyStatus->getSpecialAttackMstAttackId() !== $mstAttackSpecial->getId()) {
                $msg->push(sprintf(
                    $format,
                    'specialAttackMstAttackId',
                    $mstAttackSpecial->getId(),
                    $partyStatus->getSpecialAttackMstAttackId()
                ));
            }
        }
        if (!is_null($mstAttackNormal)) {
            if ($partyStatus->getAttackDelay() !== $mstAttackNormal->getAttackDelay()) {
                $msg->push(sprintf(
                    $format,
                    'attackDelay',
                    $mstAttackNormal->getAttackDelay(),
                    $partyStatus->getAttackDelay()
                ));
            }
            if ($partyStatus->getNextAttackInterval() !== $mstAttackNormal->getNextAttackInterval()) {
                $msg->push(sprintf(
                    $format,
                    'nextAttackInterval',
                    $mstAttackNormal->getNextAttackInterval(),
                    $partyStatus->getNextAttackInterval()
                ));
            }
        }

        if ($partyStatus->getColor() !== $mstUnit->getColor()) {
            $msg->push(sprintf(
                $format,
                'color',
                $mstUnit->getColor(),
                $partyStatus->getColor()
            ));
        }
        if ($partyStatus->getRoleType() !== $mstUnit->getRoleType()) {
            $msg->push(sprintf(
                $format,
                'roleType',
                $mstUnit->getRoleType(),
                $partyStatus->getRoleType()
            ));
        }
        if ((float)$partyStatus->getMoveSpeed() !== (float)$mstUnit->getMoveSpeed()) {
            $msg->push(sprintf(
                $format,
                'moveSpeed',
                $mstUnit->getMoveSpeed(),
                $partyStatus->getMoveSpeed()
            ));
        }
        if ($partyStatus->getSummonCost() !== $mstUnit->getSummonCost()) {
            $msg->push(sprintf(
                $format,
                'summonCost',
                $mstUnit->getSummonCost(),
                $partyStatus->getSummonCost()
            ));
        }
        if ($partyStatus->getSummonCoolTime() !== $mstUnit->getSummonCoolTime()) {
            $msg->push(sprintf(
                $format,
                'summonCoolTime',
                $mstUnit->getSummonCoolTime(),
                $partyStatus->getSummonCoolTime()
            ));
        }
        if ($partyStatus->getDamageKnockBackCount() !== $mstUnit->getDamageKnockBackCount()) {
            $msg->push(sprintf(
                $format,
                'damageKnockBackCount',
                $mstUnit->getDamageKnockBackCount(),
                $partyStatus->getDamageKnockBackCount()
            ));
        }

        // ability1~3はアンロックランクに達していない場合クライアントからは空文字で送られるので比較するマスター側も同様にする
        $ability1 = $unitAuditEntity->isAbility1Unlocked() ? $mstUnit->getMstUnitAbility1() : "";
        if ($partyStatus->getMstUnitAbility1() !== $ability1) {
            $msg->push(sprintf(
                $format,
                'mstUnitAbility1',
                $ability1,
                $partyStatus->getMstUnitAbility1()
            ));
        }
        $ability2 = $unitAuditEntity->isAbility2Unlocked() ? $mstUnit->getMstUnitAbility2() : "";
        if ($partyStatus->getMstUnitAbility2() !== $ability2) {
            $msg->push(sprintf(
                $format,
                'mstUnitAbility2',
                $ability2,
                $partyStatus->getMstUnitAbility2()
            ));
        }
        $ability3 = $unitAuditEntity->isAbility3Unlocked() ? $mstUnit->getMstUnitAbility3() : "";
        if ($partyStatus->getMstUnitAbility3() !== $ability3) {
            $msg->push(sprintf(
                $format,
                'mstUnitAbility3',
                $ability3,
                $partyStatus->getMstUnitAbility3()
            ));
        }

        return $msg;
    }

    /**
     * バトル前後のパーティステータス不一致によるチート判定を行う
     *
     * @param string $usrUserId
     * @param string $targetId 降臨バトルやステージのID
     * @param string $cheatContentType
     * @param CarbonImmutable $now
     * @param Collection<\App\Domain\Resource\Entities\PartyStatus> $opponentPartyStatuses
     * @return MstCheatSettingEntity|null チート検出されなかった場合は通常プレイヤーと判断してnullを返す
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function checkOpponentBattleStatusMismatch(
        string $usrUserId,
        string $targetId,
        string $cheatContentType,
        CarbonImmutable $now,
        Collection $opponentPartyStatuses,
    ): ?MstCheatSettingEntity {
        return $this->executeCheck(
            $usrUserId,
            $targetId,
            $cheatContentType,
            CheatType::BATTLE_STATUS_MISMATCH->value,
            $now,
            function (MstCheatSettingEntity $mstCheatSetting) use (
                $usrUserId,
                $targetId,
                $cheatContentType,
                $opponentPartyStatuses,
            ) {
                $usrCheatSession = $this->usrCheatSessionRepository->findOrCreate($usrUserId);
                $partyStatusesArray = $opponentPartyStatuses->map(function ($partyStatus) {
                    return $partyStatus->formatToLog();
                })->toArray();
                $beforeBattlePartyStatusesArray = json_decode($usrCheatSession->getPartyStatus(), true);

                if (
                    $usrCheatSession->getContentType() === $cheatContentType &&
                    $usrCheatSession->getTargetId() === $targetId &&
                    $this->arraysAreEqualRecursiveWithTolerance(
                        $beforeBattlePartyStatusesArray,
                        $partyStatusesArray
                    )
                ) {
                    return null;
                }

                return [
                    CheatConstant::LOG_DETAIL_KEY_BEFORE_BATTLE_PARTY_STATUSES => $beforeBattlePartyStatusesArray,
                    CheatConstant::LOG_DETAIL_KEY_PARTY_STATUSES => $partyStatusesArray,
                ];
            }
        );
    }
}
