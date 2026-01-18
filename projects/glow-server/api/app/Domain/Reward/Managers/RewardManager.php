<?php

declare(strict_types=1);

namespace App\Domain\Reward\Managers;

use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Reward\Entities\RewardSent;
use Illuminate\Support\Collection;

/**
 * 報酬オブジェクトの管理を行うクラス。
 * 報酬送信処理前の報酬オブジェクトと、報酬送信処理済みの報酬オブジェクトを管理する。
 * 報酬送信処理前の報酬オブジェクトの報酬送信は、RewardSendServiceで実行する。
 */
class RewardManager implements RewardManagerInterface
{
    /**
     * key: BaseReward.id
     * value: BaseReward
     *
     * 報酬報酬送信処理前の報酬オブジェクトを格納する。
     * BaseRewardを継承した報酬オブジェクトのみが格納される。
     *
     * @var array<string, BaseReward>
     */
    private array $needToSendRewards;

    /**
     * key: Rewardオブジェクトクラス名。例：IdleIncentiveReward, UserlevelUpReward, ...
     * value: array<BaseReward>
     *
     * 報酬送信済みの報酬オブジェクトを格納する。
     * BaseRewardを継承した報酬オブジェクトのみが格納される。
     * APIレスポンスなどで取得しやすいように、Rewardオブジェクトクラスごとに分けた連想配列で保持する。
     *
     * 報酬即時配布はしていないが、メールボックスへ送信した報酬などもここに含める。
     *
     * @var array<string, array<BaseReward>>
     */
    private array $sentRewards;

    public function __construct()
    {
        $this->needToSendRewards = [];
        $this->sentRewards = [];
    }

    /**
     * 報酬オブジェクトを報酬送信前リストに追加する。
     *
     * @param BaseReward $reward
     * @return void
     */
    public function addReward(BaseReward $reward): void
    {
        if ($reward->isValid() === false) {
            return;
        }

        $this->needToSendRewards[$reward->getId()] = $reward;
    }

    /**
     * 報酬オブジェクトを報酬送信前リストにまとめて追加する。
     *
     * @param Collection $rewards
     * @return void
     */
    public function addRewards(Collection $rewards): void
    {
        foreach ($rewards as $reward) {
            $this->addReward($reward);
        }
    }

    /**
     * @return Collection<string, BaseReward>
     *   key: BaseReward.id, value: BaseReward
     */
    public function getNeedToSendRewards(): Collection
    {
        return collect($this->needToSendRewards);
    }

    /**
     * 報酬送信済みリストから報酬オブジェクトを取得する。
     *
     * @param string $rewardClass
     * @return Collection<BaseReward>
     */
    public function getSentRewards(string $rewardClass): Collection
    {
        return collect($this->sentRewards[$rewardClass] ?? []);
    }

    /**
     * 報酬送信処理を実行した後に実行する処理をまとめたメソッド。
     *
     * @return void
     */
    public function afterSend(RewardSent $sentData): void
    {
        foreach ($sentData->getRewards() as $reward) {
            $this->addSentReward($reward);
        }
    }

    /**
     * 報酬送信実行済みの報酬オブジェクトを、送信済みステータスへ変更する。
     * 送信前リストから削除し、送信済みリストへ整形して追加する。
     *
     * @param BaseReward $reward
     * @return void
     */
    private function addSentReward(BaseReward $reward): void
    {
        // 送信前リストから削除
        unset($this->needToSendRewards[$reward->getId()]);

        // 送信済みリストへ追加
        // 報酬オブジェクトクラスごとに分けた連想配列で保持する
        $this->sentRewards[$reward::class][] = $reward;
    }

    public function isNeedSendRewards(): bool
    {
        return count($this->needToSendRewards) > 0;
    }
}
