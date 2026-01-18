<?php

namespace App\Filament\Pages\User;

use App\Constants\BillingStatus;
use App\Constants\RewardType;
use App\Constants\UserSearchTabs;
use App\Constants\UserStatus;
use App\Filament\Pages\BnUserSearch\BnUserSearch;
use App\Models\Adm\AdmUserBanOperateHistory;
use App\Models\Usr\UsrCurrencySummary;
use App\Models\Usr\UsrUser;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;

/**
 * プレイヤー情報画面の基底クラス
 */
abstract class UserDataBasePage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.top-page-user-search';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = '';

    protected array $breadcrumbList = [];

    public string $userId = '';
    public string $entry = '';
    public string $myId = '';
    public string $name = '';
    public ?string $lastLoginAt = '';
    public bool $isUserDetail = false;
    public int $status = 0;
    public string $message = '';
    public string $messageBackgroundColor = '';

    protected $queryString = [
        'userId',
        'entry',
    ];

    /**
     * タブ名と遷移先のURL
     */
    public array $tabGroups = [
        [
            '基本情報' => [
                UserSearchTabs::USER_PARAMETER->value => 'user-parameter',
                UserSearchTabs::TUTORIAL->value => 'user-tutorial'
            ],
            'クエスト' => [
                UserSearchTabs::QUEST->value => 'user-stage',
                UserSearchTabs::EVENT_QUEST->value => 'user-event',
                UserSearchTabs::ENHANCE_QUEST->value => 'user-enhance-quest',
                UserSearchTabs::UNLOCK_STAGE->value => 'unlock-user-stage',
                UserSearchTabs::PARTY->value => 'user-party',
            ],
            '探索' => [
                UserSearchTabs::IDLE_INCENTIVE->value => 'user-idle-incentive',
            ],
            'ショップ' => [
                UserSearchTabs::SHOP_BASIC->value => 'user-shop-item',
                UserSearchTabs::SHOP_PURCHASE->value => 'user-store-product',
                UserSearchTabs::SHOP_PASS->value => 'user-shop-pass',
                UserSearchTabs::EXCHANGE->value => 'user-exchange-lineup',
            ],
            'ゲーム内リソース' => [
                UserSearchTabs::UNIT->value => 'user-unit',
                UserSearchTabs::ITEM->value => 'user-item',
                UserSearchTabs::EMBLEM->value => 'user-emblem',
                UserSearchTabs::ARTWORK->value => 'user-artwork',
                UserSearchTabs::ENCYCLOPEDIA_RANK->value => 'user-encyclopedia-rank',
                UserSearchTabs::OUTPOST->value => 'user-outpost',
                UserSearchTabs::MAIL_BOX->value => 'user-mail-box',
            ],
            'ミッション状況' => [
                UserSearchTabs::MISSION_ACHIEVEMENT->value => 'user-mission-achievement',
                UserSearchTabs::MISSION_BEGINNER->value => 'user-mission-beginner',
                UserSearchTabs::MISSION_DAILY->value => 'user-mission-daily',
                UserSearchTabs::MISSION_WEEKLY->value => 'user-mission-weekly',
                UserSearchTabs::MISSION_DAILY_BONUS->value => 'user-mission-daily-bonus',
                UserSearchTabs::MISSION_EVENT->value => 'user-mission-event',
                UserSearchTabs::MISSION_EVENT_DAILY->value => 'user-mission-event-daily',
                UserSearchTabs::MISSION_EVENT_DAILY_BONUS->value => 'user-mission-event-daily-bonus',
                UserSearchTabs::MISSION_LIMITED_TERM->value => 'user-mission-limited-term',
                UserSearchTabs::COMEBACK_BONUS->value => 'user-comeback-bonus-progress',
            ],
            'ガシャ' => [
                UserSearchTabs::GACHA->value => 'user-gacha',
            ],
            '降臨バトル' => [
                UserSearchTabs::ADVENT_BATTLE->value => 'user-advent-battle',
            ],
            'ランクマッチ' => [
                UserSearchTabs::PVP->value => 'user-pvp',
            ],
            'ジャンプ+連携報酬' => [
                UserSearchTabs::JUMP_PLUS_REWARD->value => 'user-jump-plus-reward',
            ],
            '不正疑惑' => [
                UserSearchTabs::SUSPECTED->value => 'suspected-user-detail',
            ],
        ],
        [
            'リソース獲得消費履歴' => [
                UserSearchTabs::LOG_COIN->value => 'user-log-coin',
                UserSearchTabs::LOG_EXP->value => 'user-log-exp',
                UserSearchTabs::LOG_STAMINA->value => 'user-log-stamina',
                UserSearchTabs::LOG_ITEM->value => 'user-log-item',
                UserSearchTabs::LOG_EMBLEM->value => 'user-log-emblem',
            ],
            'キャラステータス変更履歴' => [
                UserSearchTabs::LOG_UNIT_GRADE_UP->value => 'user-log-unit-grade-up',
                UserSearchTabs::LOG_UNIT_LEVEL_UP->value => 'user-log-unit-level-up',
                UserSearchTabs::LOG_UNIT_RANK_UP->value => 'user-log-unit-rank-up',
            ],
            'プリズム獲得消費履歴' => [
                UserSearchTabs::LOG_CURRENCY_FREE->value => 'user-log-currency-free',
                UserSearchTabs::LOG_CURRENCY_PAID->value => 'user-log-currency-paid',
            ],
            'ギフト履歴' => [
                UserSearchTabs::LOG_GIFT->value => 'user-log-receive-message-reward',
            ],
            'クエスト履歴' => [
                UserSearchTabs::LOG_STAGE_ACTION->value => 'user-log-stage-action',
            ],
            'ストア履歴' => [
                UserSearchTabs::LOG_STORE->value => 'user-log-store',
                UserSearchTabs::LOG_TRADE_SHOP_ITEM->value => 'user-log-trade-shop-item',
                UserSearchTabs::LOG_EXCHANGE->value => 'log-exchange-action-page',
            ],
            '降臨バトル' => [
                UserSearchTabs::LOG_ADVENT_BATTLE_ACTION->value => 'user-log-advent-battle-action',
            ],
            'ランクマッチ' => [
                UserSearchTabs::LOG_PVP_ACTION->value => 'user-log-pvp-action',
            ],
            'ガシャ履歴' => [
                UserSearchTabs::LOG_GACHA_ACTION->value => 'user-log-gacha-action',
            ],
            '強化履歴' => [
                UserSearchTabs::LOG_OUTPOST_ENHANCEMENT->value => 'user-log-outpost-enhancement',
            ],
            'ログイン履歴' => [
                UserSearchTabs::LOG_LOGIN->value => 'user-log-login',
            ],
            'アカウント操作履歴' => [
                UserSearchTabs::LOG_SUSPECTED_USER->value => 'user-log-suspected-user',
            ],
            '原画のかけら履歴' => [
                UserSearchTabs::LOG_ARTWORK_FRAGMENT->value => 'user-log-artwork-fragment',
            ],
            '引き継ぎ履歴' => [
                UserSearchTabs::LOG_BNID_LINK->value => 'user-log-bnid-link',
            ],

        ]
    ];

    /**
     * ヘッダーで表示されるタブ名
     * 各ページでこのプロパティを上書きする
     */
    public string $currentTab = '';

    public function getTabGroups(): array
    {
        return $this->tabGroups;
    }

    public function getCurrentTab(): string
    {
        return $this->currentTab;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getHeader(): ?View
    {
        return view('filament/common/user-detail-header');
    }

    public function mount()
    {
        $this->breadcrumbList = [
            BnUserSearch::getUrl() => 'ホーム',
            UserDetail::getUrl(['userId' => $this->userId]) => 'プレイヤー詳細',
        ];

        $this->validate([
            'userId' => 'required|string',
        ]);

        $user = UsrUser::query()->withWhereHas('usr_user_profiles', function ($query) {
            $query->where('usr_user_id', $this->userId);
        })->first();

        // ユーザーが存在しない場合、ユーザー検索画面にリダイレクト
        if ($user === null) {
            Notification::make()
                ->title('該当するユーザーが存在しません。')
                ->warning()
                ->send();

            $this->redirect(UserSearch::getUrl());
        }

        $usrUserLogin = $user->usr_user_login;

        $this->myId = $user->usr_user_profiles?->my_id ?? '';
        $this->name = $user->usr_user_profiles?->name ?? '';
        $this->lastLoginAt = $usrUserLogin?->last_login_at;
        $this->isUserDetail = $this->isUserDetail();
        $this->status = $user->status;
        switch($user->status){
            case UserStatus::BAN_PERMANENT->value:
                $this->message = 'このアカウントは永久停止されています。';
                $this->messageBackgroundColor = '#F00';
                return;
            case UserStatus::BAN_TEMPORARY_CHEATING->value:
            case UserStatus::BAN_TEMPORARY_DETECTED_ANOMALY->value:
                $this->message = 'このアカウントは一時停止中です。';
                $this->messageBackgroundColor = '#d97706';
                break;
            case UserStatus::DELETED->value:
                $this->message = '対象のアカウントは、削除されています。';
                $this->messageBackgroundColor = '#F00';
                break;
        }
    }

    public function usrUserInfoList(): InfoList
    {
        $usrUser = UsrUser::query()
            ->with([
                'usr_user_profiles',
                'usr_user_login',
                'usr_user_parameter',
            ])
            ->where('id', $this->userId)
            ->first();

        $admUserBanOperateHistory = AdmUserBanOperateHistory::latest()
            ->where('usr_user_id', $this->userId)
            ->first();

        $usrUserProfile = $usrUser->usr_user_profiles;
        $usrUserLogin = $usrUser->usr_user_login;
        $usrUserParameter = $usrUser->usr_user_parameter;

        $status = $usrUser->getUserStatus();
        if ($usrUser->status !== UserStatus::NORMAL->value) {
            $status = $status .'('. $admUserBanOperateHistory?->operated_at .' 停止 )' ;
        }

        $state = [
            'level' => $usrUserParameter?->level,
            'game_start_at' => $usrUser->game_start_at,
            'last_login_at' => $usrUserLogin?->last_login_at,
            'status' => $status,
            'birth_date' => $usrUserProfile?->birth_date,
            'device' => '', //TODO:仮実装対応後再度実装する
        ];

        $fieldset = Fieldset::make('プレイヤー詳細')
            ->schema([
                TextEntry::make('level')->label('レベル')->columnSpan(2)->inlineLabel(),
                TextEntry::make('game_start_at')->label('アカウント作成日時')->columnSpan(2)->inlineLabel(),
                TextEntry::make('status')->label('アカウント稼働状況')->columnSpan(2)->inlineLabel(),
                TextEntry::make('birth_date')->label('登録生年月日')->inlineLabel(),
                TextEntry::make('device')->label('使用端末')->inlineLabel(),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function billingInfoList(): InfoList
    {
        $usrUser = UsrUser::query()
            ->with([
                'usr_store_product_history',
            ])
            ->where('id', $this->userId)
            ->first();

        $usrCurrencySummaryCollection = UsrCurrencySummary::query()
            ->where('usr_user_id', $this->userId)
            ->get(['paid_amount_apple','paid_amount_google','free_amount']);
        $usrCurrencySummary = $usrCurrencySummaryCollection->first();
        $paidAmountApple = $usrCurrencySummary?->getPaidAmountApple();
        $paidAmountGoogle = $usrCurrencySummary?->getPaidAmountGoogle();
        $paidAmount = $usrCurrencySummary?->getTotalPaidAmount();
        $freeAmount = $usrCurrencySummary?->getFreeAmount();

        $totalAmount = $paidAmount + $freeAmount;

        $jpyPurchasePrice = $usrUser->usr_store_product_history->where('currency_code', 'JPY')->sum('purchase_price') ?? 0;

        $state = [
            'billing_history' => $usrUser->usr_store_product_history->isNotEmpty() ? BillingStatus::CHARGES_APPLY->label() : BillingStatus::NO_CHARGE->label(),
            'purchase_price' => $jpyPurchasePrice,
            'totalAmount' => $totalAmount . '個　' . '内訳：　' . RewardType::PAID_DIAMOND->label() .' : ' . $paidAmount .'個　' .
            '有償プリズム内訳：AppStoreプリズム：合計' . $paidAmountApple .'個　' .
            'GooglePlayプリズム：合計 ' . $paidAmountGoogle .'個　' .
            RewardType::FREE_DIAMOND->label() . ' : ' . $freeAmount . '個',
        ];

        $fieldset = Fieldset::make('課金詳細')
            ->schema([
                TextEntry::make('billing_history')->label('課金歴')->inlineLabel(),
                TextEntry::make('purchase_price')->label('生涯課金金額（円）')->inlineLabel(),
                TextEntry::make('totalAmount')->label('所持プリズム')->columnSpan(2)->inlineLabel(),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function takeoverInfoList(): InfoList
    {
        $usrUser = UsrUser::query()
            ->with([
                'usr_device',
            ])
            ->where('id', $this->userId)
            ->first();

        $usrDevice = $usrUser->usr_device;
        $state = [
            'bn_user_id' => '引き継ぎID ' . $usrUser->bn_user_id,
            'bnid_linked_at' => $usrDevice?->bnid_linked_at
        ];

        $fieldset = Fieldset::make('引き継ぎ詳細')
            ->schema([
                TextEntry::make('bn_user_id')
                    ->label('引き継ぎ状況')
                    ->inlineLabel(),
                TextEntry::make('bn_user_id')
                    ->label('')
                    ->state('状況詳細')
                    ->url(function ($record) {
                        return; //引き継ぎ状況詳細
                    })
                    ->inlineLabel(),
                TextEntry::make('bnid_linked_at')
                    ->label('最終引継ぎ日時')
                    ->inlineLabel(),
                TextEntry::make('item_name')
                    ->label('')
                    ->formatStateUsing(fn ($state) => '詳細')
                    ->url('')
                    ->openUrlInNewTab(),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    private function isUserDetail(): bool
    {
        return 'UserDetail' === basename(str_replace('\\', '/', get_class($this)));
    }
}
