<?php

declare(strict_types=1);

namespace Tests\Support\Traits;

/**
 * 1テストメソッド内で複数のAPIリクエストを行いたい際に使用するトレイト。
 */
trait TestMultipleApiRequestsTrait
{
    private function setClassPrivateVariable(object $class, string $variableName, mixed $value): void
    {
        $reflectionClass = new \ReflectionClass($class);
        $property = $reflectionClass->getProperty($variableName);
        $property->setAccessible(true);
        $property->setValue($class, $value);
    }

    /**
     * 同一ユーザーでAPIを複数回リクエストするためにアプリケーションをリセットする。
     */
    protected function resetAppForNextRequest(string $usrUserId): void
    {
        $this->setUsrUserId($usrUserId);

        $rewardManager = app(\App\Domain\Reward\Managers\RewardManager::class);
        $this->setClassPrivateVariable($rewardManager, 'needToSendRewards', []);
        $this->setClassPrivateVariable($rewardManager, 'sentRewards', []);

        $usrModelManager = app(\App\Infrastructure\UsrModelManager::class);
        $this->setClassPrivateVariable($usrModelManager, 'usrUserId', $usrUserId);
        $this->setClassPrivateVariable($usrModelManager, 'models', []);
        $this->setClassPrivateVariable($usrModelManager, 'needSaves', []);
        $this->setClassPrivateVariable($usrModelManager, 'changedModelKeys', []);
        $this->setClassPrivateVariable($usrModelManager, 'isAllFetcheds', []);

    }
}
