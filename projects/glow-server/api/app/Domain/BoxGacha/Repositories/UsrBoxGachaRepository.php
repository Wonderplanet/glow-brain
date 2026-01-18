<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\Repositories;

use App\Domain\BoxGacha\Models\UsrBoxGacha;
use App\Domain\BoxGacha\Models\UsrBoxGachaInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Illuminate\Support\Collection;

class UsrBoxGachaRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrBoxGacha::class;

    /**
     * @param Collection<UsrBoxGachaInterface> $models
     * @return void
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrBoxGachaInterface $model) {
            return [
                'usr_user_id' => $model->getUsrUserId(),
                'mst_box_gacha_id' => $model->getMstBoxGachaId(),
                'reset_count' => $model->getResetCount(),
                'total_draw_count' => $model->getTotalDrawCount(),
                'draw_count' => $model->getDrawCount(),
                'current_box_level' => $model->getCurrentBoxLevel(),
                'draw_prizes' => json_encode($model->getDrawPrizes()->toArray()),
            ];
        })->toArray();

        UsrBoxGacha::upsert(
            $upsertValues,
            ['usr_user_id', 'mst_box_gacha_id'],
            ['reset_count', 'total_draw_count', 'draw_count', 'current_box_level', 'draw_prizes'],
        );
    }

    /**
     * 初期化済みモデルを作成（DBには保存しない）
     *
     * @param string $usrUserId
     * @param string $mstBoxGachaId
     * @return UsrBoxGachaInterface
     */
    private function makeModel(
        string $usrUserId,
        string $mstBoxGachaId
    ): UsrBoxGachaInterface {
        $model = new UsrBoxGacha();
        $model->init($usrUserId, $mstBoxGachaId);
        return $model;
    }

    /**
     * @param string $usrUserId
     * @param string $mstBoxGachaId
     * @return UsrBoxGachaInterface
     */
    private function create(
        string $usrUserId,
        string $mstBoxGachaId
    ): UsrBoxGachaInterface {
        $model = $this->makeModel($usrUserId, $mstBoxGachaId);
        $this->syncModel($model);
        return $model;
    }

    /**
     * @param string $usrUserId
     * @param string $mstBoxGachaId
     * @return UsrBoxGachaInterface|null
     */
    private function getByMstBoxGachaId(
        string $usrUserId,
        string $mstBoxGachaId
    ): ?UsrBoxGachaInterface {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_box_gacha_id',
            $mstBoxGachaId,
            function () use ($usrUserId, $mstBoxGachaId) {
                return UsrBoxGacha::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_box_gacha_id', $mstBoxGachaId)
                    ->first();
            }
        );
    }

    /**
     * @param string $usrUserId
     * @param string $mstBoxGachaId
     * @return UsrBoxGachaInterface
     */
    public function getOrCreate(
        string $usrUserId,
        string $mstBoxGachaId
    ): UsrBoxGachaInterface {
        $usrBoxGacha = $this->getByMstBoxGachaId($usrUserId, $mstBoxGachaId);
        if (is_null($usrBoxGacha)) {
            return $this->create($usrUserId, $mstBoxGachaId);
        }
        return $usrBoxGacha;
    }

    /**
     * ユーザーデータを取得（なければ新規モデルを作成するがDBには保存しない）
     *
     * @param string $usrUserId
     * @param string $mstBoxGachaId
     * @return UsrBoxGachaInterface
     */
    public function getOrMake(
        string $usrUserId,
        string $mstBoxGachaId
    ): UsrBoxGachaInterface {
        $usrBoxGacha = $this->getByMstBoxGachaId($usrUserId, $mstBoxGachaId);
        if (is_null($usrBoxGacha)) {
            return $this->makeModel($usrUserId, $mstBoxGachaId);
        }
        return $usrBoxGacha;
    }
}
