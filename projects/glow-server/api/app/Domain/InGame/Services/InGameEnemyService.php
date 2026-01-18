<?php

declare(strict_types=1);

namespace App\Domain\InGame\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\InGame\Entities\DiscoveredEnemy;
use App\Domain\InGame\Models\UsrEnemyDiscoveryInterface;
use App\Domain\InGame\Repositories\UsrEnemyDiscoveryRepository;
use App\Domain\Resource\Mst\Repositories\MstEnemyCharacterRepository;
use Illuminate\Support\Collection;

class InGameEnemyService
{
    public function __construct(
        // Repository
        private UsrEnemyDiscoveryRepository $usrEnemyDiscoveryRepository,
        private MstEnemyCharacterRepository $mstEnemyCharacterRepository,
        // Service
        private InGameMissionTriggerService $inGameMissionTriggerService,
    ) {
    }

    /**
     * ユーザーが新発見した敵情報を保存する
     *
     * @param string $usrUserId
     * @param Collection<\App\Domain\Resource\Entities\InGameDiscoveredEnemy> $discoveredEnemiesDataList
     * @param int $lapCount
     * @return Collection<\App\Domain\InGame\Models\UsrEnemyDiscoveryInterface> 新発見の敵情報
     */
    public function addNewUsrEnemyDiscoveries(
        string $usrUserId,
        Collection $discoveredEnemiesDataList,
        int $lapCount = 1,
    ): Collection {
        if ($discoveredEnemiesDataList->isEmpty()) {
            return collect();
        }

        $discoveredEnemiesDataList = $discoveredEnemiesDataList->keyBy->getMstEnemyCharacterId();

        // マスタにないエネミー情報を保存しないように、有効なIDのみ取得する
        $mstEnemyCharacters = $this->mstEnemyCharacterRepository->getByIds(
            $discoveredEnemiesDataList->keys(),
        )->keyBy->getId();
        if ($mstEnemyCharacters->isEmpty()) {
            return collect();
        }
        $mstEnemyCharacterIds = $mstEnemyCharacters->keys();

        $usrEnemyDiscoveries = $this->usrEnemyDiscoveryRepository->getByMstEnemyCharacterIds(
            $usrUserId,
            $mstEnemyCharacterIds,
        );

        $knownMstEnemyCharacterIds = $usrEnemyDiscoveries
            ->mapWithKeys(fn(UsrEnemyDiscoveryInterface $model) => [
                $model->getMstEnemyCharacterId() => $model->getMstEnemyCharacterId(),
            ]);

        $newUsrEnemyDiscoveries = collect();
        $discoveredEnemies = collect();
        foreach ($mstEnemyCharacterIds as $mstEnemyCharacterId) {
            $isNew = $knownMstEnemyCharacterIds->has($mstEnemyCharacterId) === false;

            $isNew && $newUsrEnemyDiscoveries->push(
                $this->usrEnemyDiscoveryRepository->create($usrUserId, $mstEnemyCharacterId)
            );

            $mstEnemyCharacter = $mstEnemyCharacters->get($mstEnemyCharacterId);
            /** @var null|\App\Domain\Resource\Entities\InGameDiscoveredEnemy $discoveredEnemyData */
            $discoveredEnemyData = $discoveredEnemiesDataList->get($mstEnemyCharacterId);
            if ($mstEnemyCharacter === null || $discoveredEnemyData === null) {
                continue;
            }

            $discoveredEnemies->push(
                new DiscoveredEnemy(
                    $mstEnemyCharacter,
                    $discoveredEnemyData->getCount(),
                    $isNew,
                )
            );
        }

        $this->usrEnemyDiscoveryRepository->syncModels($newUsrEnemyDiscoveries);

        // ミッショントリガー
        $this->inGameMissionTriggerService->sendDiscoveredEnemyTriggers($discoveredEnemies, $lapCount);

        return $newUsrEnemyDiscoveries;
    }

    /**
     * 敵の図鑑を取得済みにする
     * @param string $usrUserId
     * @param string $mstEnemyCharacterId
     * @throws GameException
     */
    public function markAsCollected(string $usrUserId, string $mstEnemyCharacterId): void
    {
        $usrEnemeyDiscovery =  $this->usrEnemyDiscoveryRepository->getByMstEnemyCharacterIds(
            $usrUserId,
            collect([$mstEnemyCharacterId])
        )->first();

        // データがない
        if (is_null($usrEnemeyDiscovery)) {
            throw new GameException(
                ErrorCode::ENCYCLOPEDIA_DATA_NOT_FOUND,
                'enemyDiscovery encyclopedia is new data not found. (' . $mstEnemyCharacterId . ')'
            );
        }
        // 取得したデータのis_new_encyclopediaが1かどうか
        if ($usrEnemeyDiscovery->isAlreadyCollected()) {
            // 取得したデータのis_new_encyclopediaが1でない
            throw new GameException(
                ErrorCode::ENCYCLOPEDIA_NOT_IS_NEW,
                'enemyDiscovery encyclopedia not is new data . (' . $mstEnemyCharacterId . ')'
            );
        }
        $usrEnemeyDiscovery->markAsCollected();
        $this->usrEnemyDiscoveryRepository->syncModel($usrEnemeyDiscovery);
    }
}
