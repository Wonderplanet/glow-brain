<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserParameterInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrUserParameterRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrUserParameter::class;

    /**
     * UsrModelManagerから呼び出されるメソッド。
     * 各UseCaseの最後のsaveAllで実行されるため、基本的には直接使用する必要はありません。
     *
     * @param Collection $models
     * @return void
     */
    protected function saveModels(Collection $models): void
    {
        foreach ($models as $model) {
            if ($this->isValidModel($model) === false) {
                continue;
            }

            // モデルのDB保存
            $model->save();
        }
    }

    /**
     * UsrUserParameterを取得する
     *
     * UsrUserParameter取得時は基本的にスタミナ自然回復を適用状態としたいので
     * 基本的にこの関数ではなくUserService->recoverStaminaを使うようにしてください
     *
     * @throws GameException
     */
    public function findByUsrUserId(string $usrUserId): UsrUserParameterInterface
    {
        $usrUserParameter = $this->cachedGetOne($usrUserId);
        if ($usrUserParameter === null) {
            throw new GameException(ErrorCode::USER_NOT_FOUND);
        }

        return $usrUserParameter;
    }

    public function create(string $usrUserId, int $initialStamina, CarbonImmutable $now): UsrUserParameterInterface
    {
        $usrUserParameter = new UsrUserParameter();
        $usrUserParameter->init($usrUserId, $initialStamina, $now);

        $this->syncModel($usrUserParameter);

        return $usrUserParameter;
    }

    public function useCoin(string $usrUserId, int $consumeCoin): UsrUserParameterInterface
    {
        $usrUserParameter = $this->findByUsrUserId($usrUserId);
        $newCoin = $usrUserParameter->getCoin() - $consumeCoin;
        if ($newCoin < 0) {
            throw new GameException(ErrorCode::LACK_OF_RESOURCES);
        }
        $usrUserParameter->setCoin($newCoin);
        $this->syncModel($usrUserParameter);
        return $usrUserParameter;
    }
}
