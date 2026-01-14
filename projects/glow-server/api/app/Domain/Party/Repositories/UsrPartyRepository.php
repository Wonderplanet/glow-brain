<?php

declare(strict_types=1);

namespace App\Domain\Party\Repositories;

use App\Domain\Party\Models\Eloquent\UsrParty as EloquentUsrParty;
use App\Domain\Party\Models\UsrParty;
use App\Domain\Party\Models\UsrPartyInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRawRepository;
use Illuminate\Support\Collection;

class UsrPartyRepository extends UsrModelMultiCacheRawRepository
{
    protected string $modelClass = UsrParty::class;

    /**
     * @param Collection<UsrPartyInterface> $models
     * @return void
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrPartyInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'party_no' => $model->getPartyNo(),
                'party_name' => $model->getPartyName(),
                'usr_unit_id_1' => $model->getUsrUnitId1(),
                'usr_unit_id_2' => $model->getUsrUnitId2(),
                'usr_unit_id_3' => $model->getUsrUnitId3(),
                'usr_unit_id_4' => $model->getUsrUnitId4(),
                'usr_unit_id_5' => $model->getUsrUnitId5(),
                'usr_unit_id_6' => $model->getUsrUnitId6(),
                'usr_unit_id_7' => $model->getUsrUnitId7(),
                'usr_unit_id_8' => $model->getUsrUnitId8(),
                'usr_unit_id_9' => $model->getUsrUnitId9(),
                'usr_unit_id_10' => $model->getUsrUnitId10(),
            ];
        })->toArray();

        EloquentUsrParty::upsert(
            $upsertValues,
            ['usr_user_id', 'party_no'],
            [
                'party_name',
                'usr_unit_id_1',
                'usr_unit_id_2',
                'usr_unit_id_3',
                'usr_unit_id_4',
                'usr_unit_id_5',
                'usr_unit_id_6',
                'usr_unit_id_7',
                'usr_unit_id_8',
                'usr_unit_id_9',
                'usr_unit_id_10',
            ]
        );
    }

    public function create(string $usrUserId, int $partyNo, Collection $usrUnitIds): UsrPartyInterface
    {
        $usrParty = UsrParty::create(
            usrUserId: $usrUserId,
            partyNo: $partyNo,
            usrUnitIds: $usrUnitIds->unique()->values(),
        );

        $this->syncModel($usrParty);
        return $usrParty;
    }

    /**
     * @return Collection<UsrPartyInterface>
     */
    public function getList(string $usrUserId): Collection
    {
        return $this->cachedGetAll($usrUserId);
    }

    public function getByPartyNo(string $usrUserId, int $partyNo): ?UsrPartyInterface
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'party_no',
            $partyNo,
            dbCallback: function () use ($usrUserId, $partyNo) {
                $record = UsrParty::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('party_no', $partyNo)
                    ->first();

                if ($record === null) {
                    return null;
                }

                return UsrParty::createFromRecord($record);
            }
        );
    }
}
