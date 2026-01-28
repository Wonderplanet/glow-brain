<?php

namespace App\Livewire;

use App\Models\Usr\UsrCurrencyFree;
use App\Models\Usr\UsrCurrencySummary;
use App\Models\Usr\UsrUserProfile;
use Filament\Widgets\Widget;

class UsrUserCurrencyHeldOverView extends Widget
{
    protected static string $view = 'livewire.usr-user-currency-held-over-view';

    protected $listeners = [
        'userIdUpdated' => 'onUserIdUpdated',
    ];

    public string $userId = '';

    public string $userName = '';

    // ゲーム内、配布などで取得した無償一次通貨所持枚数
    public int $ingameAmount = 0;

    // ショップ販売の追加付与で取得した無償一次通貨所持枚数
    public int $bonusAmount = 0;

    // 広告視聴などで取得した無償一次通貨所持枚数
    public int $rewardAmount = 0;

    // 無償一次通貨の合計
    public int $totalFreeAmount = 0;

    // 有償無償一次通貨合計所持枚数
    public int $primaryCurrencyAmount = 0;

    // 有償一次通貨の合計(apple + google)
    public int $totalPaidAmount = 0;

    public int $paidAmountApple = 0;

    public int $paidAmountGoogle = 0;

    // 二次通貨の所持枚数
    public int $summariesCash = 0;

    public bool $existsUsrUser = false;

    public bool $existsUsrCurrencyFree = false;

    public bool $existsUsrCurrencySummary = false;

    public function mount(string $userId): void
    {
        $this->userId = $userId;
        $this->setUserCurrencyData();

    }

    public function onUserIdUpdated($userId): void
    {
        $this->userId = $userId;
        $this->userName = '';
        $this->ingameAmount = 0;
        $this->bonusAmount = 0;
        $this->rewardAmount = 0;
        $this->totalFreeAmount = 0;
        $this->primaryCurrencyAmount = 0;
        $this->totalPaidAmount = 0;
        $this->paidAmountApple = 0;
        $this->paidAmountGoogle = 0;
        $this->summariesCash = 0;
        $this->existsUsrUser = false;
        $this->existsUsrCurrencyFree = false;
        $this->existsUsrCurrencySummary = false;
        $this->setUserCurrencyData();
    }

    protected function setUserCurrencyData(): void
    {
        $usrUserProfileCollection = UsrUserProfile::query()
            ->where('usr_user_id', $this->userId)
            ->get(['name']);

        $usrCurrencyFreeCollection = UsrCurrencyFree::query()
            ->where('usr_user_id', $this->userId)
            ->get(['ingame_amount', 'bonus_amount', 'reward_amount']);

        $usrCurrencySummaryCollection = UsrCurrencySummary::query()
            ->where('usr_user_id', $this->userId)
            ->get(['paid_amount_apple','paid_amount_google']);

        if ($usrUserProfileCollection->isNotEmpty()) {
            /** @var UsrUserProfile $usrUser */
            $usrUserProfile = $usrUserProfileCollection->first();
            $this->userName = $usrUserProfile->getName();
            $this->existsUsrUser = true;
        }

        if ($usrCurrencyFreeCollection->isNotEmpty()) {
            /** @var UsrCurrencyFree $usrCurrencyFree */
            $usrCurrencyFree = $usrCurrencyFreeCollection->first();

            $this->ingameAmount = $usrCurrencyFree->getIngameAmount();
            $this->bonusAmount = $usrCurrencyFree->getBonusAmount();
            $this->rewardAmount = $usrCurrencyFree->getRewardAmount();
            $this->totalFreeAmount = $usrCurrencyFree->getTotalAmount();
            $this->primaryCurrencyAmount = $usrCurrencyFree->getTotalAmount();
            $this->existsUsrCurrencyFree = true;
        }

        if ($usrCurrencySummaryCollection->isNotEmpty()) {
            /** @var UsrCurrencySummary $usrCurrencySummary */
            $usrCurrencySummary = $usrCurrencySummaryCollection->first();

            $this->paidAmountApple = $usrCurrencySummary->getPaidAmountApple();
            $this->paidAmountGoogle = $usrCurrencySummary->getPaidAmountGoogle();
            $this->totalPaidAmount = $usrCurrencySummary->getTotalPaidAmount();
            $this->primaryCurrencyAmount += $this->totalPaidAmount;
            //$this->summariesCash = $usrCurrencySummary->getCash();
            $this->existsUsrCurrencySummary = true;
        }
    }
}
