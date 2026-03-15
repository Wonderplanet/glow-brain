<?php

declare(strict_types=1);

namespace App\Domain\Reward\Traits;

use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Enums\UnreceivedRewardReason;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddFreeCurrencyOverByMaxException;

trait RewardSendServiceTrait
{
    /**
     * 報酬を送信済みとしてマークする
     *
     * @param Collection<BaseReward> $rewards
     * @return void
     */
    public function markRewardsAsSent(Collection $rewards): void
    {
        foreach ($rewards as $reward) {
            $reward->markAsSent();
        }
    }

    /**
     * メールボックスへ送信する報酬としてマークする
     * ※ この時点では、メールボックスへの送信実行はまだ。
     *
     * @param Collection<BaseReward> $rewards
     * @return void
     */
    public function markRewardsAsSentToMessage(Collection $rewards): void
    {
        foreach ($rewards as $reward) {
            $reward->setUnreceivedRewardReason(UnreceivedRewardReason::SENT_TO_MESSAGE);
        }
    }

    /**
     * 報酬配布を実行し、リソース上限超過でエラーが発生したら、メールボックスへ送信するようにマークする
     * @param Collection<BaseReward> $rewards
     * @param callable $sendCallback 報酬配布を実行する処理のコールバック関数
     * @return void
     */
    public function trySendRewardsOrMarkAsSentToMessage(
        Collection $rewards,
        callable $sendCallback,
    ): void {
        try {
            $sendCallback();
            $this->markRewardsAsSent($rewards);
        } catch (\Throwable $e) {
            if ($this->isCurrencyOverflowException($e)) {
                $this->markRewardsAsSentToMessage($rewards);
                return;
            }

            // その他の例外はそのまま投げる
            throw $e;
        }
    }

    /**
     * 例外がプリズム上限超過のものかどうかをチェックする
     * @return bool true: プリズム上限超過の例外, false: それ以外の例外
     */
    public function isCurrencyOverflowException(\Throwable $e): bool
    {
        return $e instanceof WpCurrencyAddCurrencyOverByMaxException
            || $e instanceof WpCurrencyAddFreeCurrencyOverByMaxException;
    }
}
