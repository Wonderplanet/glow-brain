<?php

declare(strict_types=1);

namespace App\Domain\Party\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Party\Constants\PartyConstant;
use App\Domain\Party\Models\UsrPartyInterface;
use App\Domain\Party\Repositories\UsrPartyRepository;
use App\Domain\Resource\Entities\Party;
use App\Domain\Resource\Entities\PartyStatus;
use App\Domain\Resource\Mst\Services\MstNgWordService;
use App\Domain\Unit\Delegators\UnitDelegator;
use Illuminate\Support\Collection;

class PartyService
{
    public function __construct(
        private MstNgWordService $mstNgWordService,
        private UsrPartyRepository $usrPartyRepository,
        // Delegator
        private UnitDelegator $unitDelegator,
    ) {
    }

    /**
     * @param string       $usrUserId
     * @param array<mixed> $parties
     * @param Collection   $hasUsrUnitIds 所持しているキャラのusr_unit_id
     * @return Collection
     * @throws GameException
     */
    public function saveParties(string $usrUserId, array $parties, Collection $hasUsrUnitIds): Collection
    {
        $usrParties = $this
            ->usrPartyRepository
            ->getList($usrUserId)
            ->keyBy(fn(UsrPartyInterface $usrParty) => $usrParty->getPartyNo());

        $resultParties = collect();
        foreach ($parties as $party) {
            $this->validatePartyNo($party['partyNo']);

            // setUnitsでインデックスアクセスしているのでkeyをリセットしてCollection化する
            $units = collect(array_values($party['units']));
            $this->validatePartyUnits($units, $hasUsrUnitIds);

            // ユーザーパーティの取得
            $usrParty = $usrParties->get($party['partyNo']);
            // ユーザーパーティが存在しない場合は新規作成
            if (is_null($usrParty)) {
                $usrParty = $this->usrPartyRepository->create($usrUserId, $party['partyNo'], $units);
            }

            if ($usrParty->getPartyName() !== $party['partyName']) {
                // パーティ名が変更されている場合はバリデーションを行う
                $this->validatePartyName($party['partyName']);
            }

            $usrParty->setPartyName($party['partyName']);
            $usrParty->setUnits($units);
            $this->usrPartyRepository->syncModel($usrParty);

            $resultParties->add($usrParty);
        }
        return $resultParties;
    }

    /**
     * パーティ番号のバリデーション
     * @param int $partyNo
     * @throws GameException
     */
    private function validatePartyNo(int $partyNo): void
    {
        if ($partyNo < PartyConstant::FIRST_PARTY_NO || $partyNo > PartyConstant::INITIAL_PARTY_COUNT) {
            // パーティ番号異常
            throw new GameException(
                ErrorCode::PARTY_INVALID_PARTY_NO,
                "partyNo is invalid. (partyNo: $partyNo)"
            );
        }
    }

    /**
     * パラメータのusr_unit_idを検証する
     * @param Collection $usrUnitIds 検証対象のusr_unit_id
     * @param Collection $hasUsrUnitIds 所持しているユニットのusr_unit_id
     * @return void
     * @throws GameException
     */
    private function validatePartyUnits(Collection $usrUnitIds, Collection $hasUsrUnitIds): void
    {
        if ($usrUnitIds->isEmpty() || $usrUnitIds->count() > PartyConstant::MAX_UNIT_COUNT_IN_PARTY) {
            // ユニット数異常
            throw new GameException(
                ErrorCode::PARTY_INVALID_UNIT_COUNT,
                "party unit count is invalid. (unit count: " . $usrUnitIds->count() . ")"
            );
        }

        if ($usrUnitIds->duplicates()->isNotEmpty()) {
            // パーティ内でのユニット重複
            throw new GameException(
                ErrorCode::PARTY_DUPLICATE_UNIT_ID,
                "duplicate unit in party."
            );
        }

        $usrUnitIds->each(function (string $usrUnitId) use ($hasUsrUnitIds) {
            if (!$hasUsrUnitIds->contains($usrUnitId)) {
                // ユニットが存在しない
                throw new GameException(
                    ErrorCode::PARTY_INVALID_UNIT_ID,
                    "unit is not found. (unitId: $usrUnitId)"
                );
            }
        });
    }

    /**
     * パーティ名のバリデーション
     * @param string $partyName
     * @see https://wonderplanet.atlassian.net/wiki/spaces/GLOW/pages/195002370/11-2#11-2-3-1_%E3%83%86%E3%82%AD%E3%82%B9%E3%83%88%E5%85%A5%E5%8A%9B%E3%83%9C%E3%83%83%E3%82%AF%E3%82%B9
     * @throws GameException
     */
    private function validatePartyName(string $partyName): void
    {
        if ($partyName === '') {
            throw new GameException(ErrorCode::PARTY_INVALID_PARTY_NAME, 'partyName is empty.');
        }

        // 文字数超過
        if (mb_strlen($partyName) > PartyConstant::MAX_PARTY_NAME_LENGTH) {
            throw new GameException(ErrorCode::PARTY_INVALID_PARTY_NAME, 'partyName is too long.');
        }

        $this->mstNgWordService->validateNGWord($partyName, ErrorCode::PARTY_INVALID_PARTY_NAME);
    }

    /**
     * パーティのユニット情報を取得する。
     * パーティ内でのユニットの並び順を変えずに取得する。
     *
     * @param string $usrUserId
     * @param int    $partyNo
     * @return Party
     */
    public function getParty(string $usrUserId, int $partyNo): Party
    {
        $usrParty = $this->usrPartyRepository->getByPartyNo($usrUserId, $partyNo);
        if ($usrParty === null) {
            return new Party(null, collect());
        }

        $usrUnitIds = $usrParty->getUsrUnitIds();
        $units = $this->unitDelegator->fetchUnitDataByUsrUnitIds($usrUserId, $usrUnitIds);
        return new Party($usrParty->toEntity(), $units);
    }

    /**
     * パーティステータスを生成する
     *
     * @param Collection<array<mixed>> $partyStatuses
     * @return Collection<PartyStatus>
     */
    public function makePartyStatusList(Collection $partyStatuses): Collection
    {
        return $partyStatuses->map(function ($status) {
            return new PartyStatus(
                $status['usrUnitId'] ?? '',
                $status['mstUnitId'],
                $status['color'],
                $status['roleType'],
                $status['hp'],
                $status['atk'],
                (string) $status['moveSpeed'],
                $status['summonCost'],
                $status['summonCoolTime'],
                $status['damageKnockBackCount'],
                $status['specialAttackMstAttackId'],
                $status['attackDelay'],
                $status['nextAttackInterval'],
                $status['mstUnitAbility1'],
                $status['mstUnitAbility2'],
                $status['mstUnitAbility3'],
            );
        });
    }
}
