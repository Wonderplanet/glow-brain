<?php

namespace Tests\Feature\Domain\JumpPlus\Repositories;

use App\Domain\JumpPlus\Repositories\DynJumpPlusRewardRepository;

/**
 * DynJumpPlusRewardRepositoryのテスト用クラス
 */
class TestDynJumpPlusRewardRepository extends DynJumpPlusRewardRepository
{
    // テスト用にdev-ld環境のdynamodbテーブルを利用する
    protected function makeTableName(): string
    {
        return 'dyn-dev-ld-jump-plus-rewards';
    }

    public function getTestTableName(): string
    {
        return $this->tableName;
    }
}
