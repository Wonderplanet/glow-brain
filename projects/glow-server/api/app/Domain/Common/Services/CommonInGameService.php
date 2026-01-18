<?php

declare(strict_types=1);

namespace App\Domain\Common\Services;

use App\Domain\Resource\Entities\InGameDiscoveredEnemy;
use Illuminate\Support\Collection;

/**
 * ステージや降臨バトルなどのインゲーム関連処理を共通化するためのサービスクラス
 */
class CommonInGameService
{
    /**
     * 発見した敵キャラ情報配列をInGameDiscoveredEnemyDataインスタンスに詰め込む
     *
     * @param array<mixed> $discoveredEnemies リクエストパラメータから受け取った、発見した敵キャラ情報配列
     * @return Collection<InGameDiscoveredEnemy>
     */
    public function makeDiscoveredEnemyDataList(array $discoveredEnemies): Collection
    {
        $dataList = collect();

        foreach ($discoveredEnemies as $discoveredEnemy) {
            $dataList->push(new InGameDiscoveredEnemy(
                $discoveredEnemy['mstEnemyCharacterId'],
                $discoveredEnemy['count'],
            ));
        }

        return $dataList;
    }
}
