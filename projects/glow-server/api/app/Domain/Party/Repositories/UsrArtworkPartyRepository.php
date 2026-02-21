<?php

declare(strict_types=1);

namespace App\Domain\Party\Repositories;

use App\Domain\Party\Models\Eloquent\UsrArtworkParty as EloquentUsrArtworkParty;
use App\Domain\Party\Models\UsrArtworkParty;
use App\Domain\Party\Models\UsrArtworkPartyInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRawRepository;
use Illuminate\Support\Collection;

/**
 * NOTE: 現状は1ユーザー1パーティだが、将来的に複数パーティ対応の可能性があるため
 * UsrModelMultiCacheRawRepositoryを継承している。
 */
class UsrArtworkPartyRepository extends UsrModelMultiCacheRawRepository
{
    protected string $modelClass = UsrArtworkParty::class;

    /**
     * @param Collection<UsrArtworkPartyInterface> $models
     * @return void
     */
    protected function saveModels(Collection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        $upsertValues = $models->map(function (UsrArtworkPartyInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'party_no' => $model->getPartyNo(),
                'party_name' => $model->getPartyName(),
                'mst_artwork_id_1' => $model->getMstArtworkId1(),
                'mst_artwork_id_2' => $model->getMstArtworkId2(),
                'mst_artwork_id_3' => $model->getMstArtworkId3(),
                'mst_artwork_id_4' => $model->getMstArtworkId4(),
                'mst_artwork_id_5' => $model->getMstArtworkId5(),
                'mst_artwork_id_6' => $model->getMstArtworkId6(),
                'mst_artwork_id_7' => $model->getMstArtworkId7(),
                'mst_artwork_id_8' => $model->getMstArtworkId8(),
                'mst_artwork_id_9' => $model->getMstArtworkId9(),
                'mst_artwork_id_10' => $model->getMstArtworkId10(),
            ];
        })->toArray();

        EloquentUsrArtworkParty::upsert(
            $upsertValues,
            ['usr_user_id', 'party_no'],
            [
                'party_name',
                'mst_artwork_id_1',
                'mst_artwork_id_2',
                'mst_artwork_id_3',
                'mst_artwork_id_4',
                'mst_artwork_id_5',
                'mst_artwork_id_6',
                'mst_artwork_id_7',
                'mst_artwork_id_8',
                'mst_artwork_id_9',
                'mst_artwork_id_10',
            ]
        );
    }

    public function create(string $usrUserId, int $partyNo, Collection $mstArtworkIds): UsrArtworkPartyInterface
    {
        $usrArtworkParty = UsrArtworkParty::create(
            usrUserId: $usrUserId,
            partyNo: $partyNo,
            mstArtworkIds: $mstArtworkIds->unique()->values(),
        );

        $this->syncModel($usrArtworkParty);
        return $usrArtworkParty;
    }

    /**
     * 現状は1ユーザー1パーティのため、party_no=1固定で取得
     */
    public function get(string $usrUserId): ?UsrArtworkPartyInterface
    {
        return $this->getByPartyNo($usrUserId, 1);
    }

    private function getByPartyNo(string $usrUserId, int $partyNo): ?UsrArtworkPartyInterface
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'party_no',
            $partyNo,
            dbCallback: function () use ($usrUserId, $partyNo) {
                $record = UsrArtworkParty::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('party_no', $partyNo)
                    ->first();

                if ($record === null) {
                    return null;
                }

                return UsrArtworkParty::createFromRecord($record);
            }
        );
    }
}
