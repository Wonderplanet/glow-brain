<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use WonderPlanet\Domain\Currency\Models\UsrCurrencyFree;

/**
 * 無料一次通貨を管理するRepository
 */
class UsrCurrencyFreeRepository
{
    /**
     * 無償一次通貨の登録を行う
     *
     * @param string $userId
     * @param integer $ingameAmont
     * @param integer $bonusAmount
     * @param integer $rewardAmount
     * @return void
     */
    public function insertFreeCurrency(string $userId, int $ingameAmont, int $bonusAmount, int $rewardAmount): void
    {
        $usrCurrencyFree = new UsrCurrencyFree();
        $usrCurrencyFree->usr_user_id = $userId;
        $usrCurrencyFree->ingame_amount = $ingameAmont;
        $usrCurrencyFree->bonus_amount = $bonusAmount;
        $usrCurrencyFree->reward_amount = $rewardAmount;
        $usrCurrencyFree->save();
    }

    /**
     * 無償一次通貨の追加を行う
     *
     * incrementすることでプログラム処理で値が巻き戻る可能性を防ぐ
     *
     * @param string $userId
     * @param integer $ingameAmont
     * @param integer $bonusAmount
     * @param integer $rewardAmount
     * @return void
     */
    public function incrementFreeCurrency(string $userId, int $ingameAmont, int $bonusAmount, int $rewardAmount): void
    {
        UsrCurrencyFree::query()
            ->where('usr_user_id', $userId)
            ->incrementEach([
                'ingame_amount' => $ingameAmont,
                'bonus_amount' => $bonusAmount,
                'reward_amount' => $rewardAmount,
            ]);
    }

    /**
     * 無償一次通貨の減算を行う
     *
     * decrementすることでプログラム処理で値が巻き戻る可能性を防ぐ
     *
     * @param string $userId
     * @param integer $ingameAmont
     * @param integer $bonusAmount
     * @param integer $rewardAmount
     * @return void
     */
    public function decrementFreeCurrency(string $userId, int $ingameAmont, int $bonusAmount, int $rewardAmount): void
    {
        UsrCurrencyFree::query()
            ->where('usr_user_id', $userId)
            ->decrementEach([
                'ingame_amount' => $ingameAmont,
                'bonus_amount' => $bonusAmount,
                'reward_amount' => $rewardAmount,
            ]);
    }

    /**
     * 無償一次通貨情報を取得する
     *
     * @param string $userId
     * @return UsrCurrencyFree|null
     */
    public function findByUserId(string $userId): ?UsrCurrencyFree
    {
        return UsrCurrencyFree::query()->where('usr_user_id', $userId)->first() ?? null;
    }

    /**
     * ユーザーの無償一次通貨情報を論理削除する
     *
     * @param string $userId
     * @return void
     */
    public function softDeleteByUserId(string $userId): void
    {
        UsrCurrencyFree::query()->where('usr_user_id', $userId)->delete();
    }
}
