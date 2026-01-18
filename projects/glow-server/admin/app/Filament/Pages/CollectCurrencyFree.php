<?php

namespace App\Filament\Pages;

use App\Constants\Database;
use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Traits\DatabaseTransactionTrait;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyAdminDelegator;

/**
 * 無償一次通貨の回収
 */
class CollectCurrencyFree extends Page
{
    use Authorizable;
    use InteractsWithFormActions;
    use DatabaseTransactionTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.collect-currency-free';
    protected static ?int $navigationSort = 110;

    protected static ?string $navigationGroup = NavigationGroups::CS->value;
    protected static ?string $title = '無償一次通貨回収';

    public array $userData = [];

    /**
     * 検索フォームのデータを格納する
     *
     * @var array
     */
    public array $searchFormData = [
        'userId' => ''
    ];

    /**
     * 回収フォームのデータを格納する
     *
     * @var array
     */
    public array $collectFormData = [
        'userId' => ''
    ];

    /**
     * このページで使用するフォームメソッド
     *
     * @return array
     */
    protected function getForms(): array
    {
        return [
            'searchForm',
            'collectForm',
        ];
    }

    // ########################################################
    // 検索用フォーム
    // ########################################################
    /**
     * 検索用フォーム
     *
     * @param Form $form
     * @return Form
     */
    public function searchForm(Form $form): Form
    {
        return $form->schema([
            // ユーザーID
            Forms\Components\TextInput::make('userId')
                ->label(new HtmlString("<p class='text-2xl'>ユーザー検索</p>"))
                ->placeholder('ユーザーIDを入力')
                ->columnSpanFull(),
        ])
            ->statePath('searchFormData');
    }

    /**
     * 検索用フォームで使用するアクションボタンを返す
     *
     * @return array
     */
    public function getSearchFormActoins(): array
    {
        return [
            $this->searchButton(),
        ];
    }


    /**
     * 検索用ボタン
     *
     * @return Action
     */
    public function searchButton(): Action
    {
        return Action::make('searchButton')
            ->label('検索')
            ->action(function () {
                $this->search();
            });
    }

    /**
     * 検索を実行する
     *
     * @return void
     */
    private function search(): void
    {
        $userId = $this->searchFormData['userId'];

        $this->collectFormData['userId'] = $userId;
        $this->getUserData($userId);
    }

    /**
     * ユーザー情報を取得する
     *
     * @param string $userId
     * @return void
     */
    private function getUserData(string $userId): void
    {
        $this->userData = [];

        /** @var CurrencyAdminDelegator $currencyAdminDelegator */
        $currencyAdminDelegator = app()->make(CurrencyAdminDelegator::class);

        /** @var \Wonderplanet\domain\Currency\Entities\UsrCurrencySummaryEntity|null $summary */
        $summary = $currencyAdminDelegator->getCurrencySummary($userId);

        // 無償通貨の内訳を取得
        /** @var \Wonderplanet\domain\Currency\Entities\UsrCurrencyFreeEntity|null $usrCurrencyFree */
        $usrCurrencyFree = $currencyAdminDelegator->getCurrencyFree($userId);

        if (!is_null($summary)) {
            $this->userData = [
                'userId' => $userId,
                'totalAmount' => $summary->getTotalAmount(),
                'paidAmount' => $summary->getTotalPaidAmount(),
                'paidAmountApple' => $summary->getPaidAmountApple(),
                'paidAmountGoogle' => $summary->getPaidAmountGoogle(),
                'freeAmount' => $summary->getFreeAmount(),
                'freeIngameAmount' => $usrCurrencyFree->getIngameAmount(),
                'freeBonusAmount' => $usrCurrencyFree->getBonusAmount(),
                'freeRewardAmount' => $usrCurrencyFree->getRewardAmount(),
            ];
        }
    }

    // ########################################################
    // 回収用フォーム
    // ########################################################
    /**
     * 回収用フォーム
     */
    public function collectForm(Form $form): Form
    {
        return $form->schema([
            // ユーザーID
            TextInput::make('userId')
                ->label('ユーザーID')
                ->hidden()
                ->required(),
            // 回収対象の無償通貨タイプ
            Select::make('type')
                ->label('回収対象の無償通貨タイプ')
                ->options([
                    CurrencyConstants::FREE_CURRENCY_TYPE_INGAME => 'ゲーム内配布',
                    CurrencyConstants::FREE_CURRENCY_TYPE_BONUS => '購入ボーナス',
                    CurrencyConstants::FREE_CURRENCY_TYPE_REWARD => '広告リワード',
                ])
                ->required(),
            // 回収対象の無償通貨数
            TextInput::make('amount')
                ->label('回収対象の無償通貨数')
                ->numeric()
                ->required(),
            // 回収理由
            Textarea::make('triggerDetail')
                ->label('コメント (trigger_detailに記録されます)')
                ->required(),
        ])
            ->statePath('collectFormData');
    }

    /**
     * 回収用フォームで使用するアクションボタンを返す
     *
     * @return array
     */
    public function getCollectFormActions(): array
    {
        return [
            $this->collectButton(),
        ];
    }

    /**
     * 回収実行ボタン
     *
     * @return Action
     */
    public function collectButton(): Action
    {
        return Action::make('collectButton')
            ->label('回収')
            ->requiresConfirmation()
            ->action(function () {
                $this->collect();

                return Action::make('success')
                    ->label('回収完了')
                    ->success();
            });
    }

    /**
     * 回収を実行する
     *
     * @return void
     */
    public function collect(): void
    {
        // 入力値のバリデーション
        $this->validate();

        // 回収実行
        try {
            $this->transaction(
                function () {
                    $userId = $this->collectFormData['userId'];
                    $type = $this->collectFormData['type'];
                    $amount = $this->collectFormData['amount'];
                    $triggerDetail = $this->collectFormData['triggerDetail'];

                    /** @var CurrencyAdminDelegator $currencyAdminDelegator */
                    $currencyAdminDelegator = app()->make(CurrencyAdminDelegator::class);

                    // 管理ツールから登録するため、ログに記録するosPlatformはADMINTOOL固定
                    $osPlatform = CurrencyConstants::OS_PLATFORM_ADMINTOOL;

                    // 無償通貨の回収
                    $currencyAdminDelegator->collectFreeCurrency(
                        $userId,
                        $osPlatform,
                        $type,
                        $amount,
                        $triggerDetail,
                    );

                    // 成功の通知
                    $this->notice('無償一次通貨の回収が完了しました', 'success');

                    // userDataの更新
                    $this->getUserData($userId);

                    // フォームの初期化
                    $this->collectFormData = [
                        'userId' => $userId,
                    ];

                    // 完了ダイアログを出す
                    $this->replaceMountedAction(
                        'successButton',
                        ['description' => '無償一次通貨の回収が完了しました']
                    );
                },
                [
                    Database::TIDB_CONNECTION
                ]
            );
        } catch (\Exception $e) {
            Log::error('', [$e]);
            $this->notice('無償一次通貨の回収に失敗しました', 'danger');

            // 失敗ダイアログを出す
            $this->replaceMountedAction(
                'errorButton',
                [
                    'description' => '無償一次通貨の回収に失敗しました',
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    // ########################################################
    // 共通メソッド
    // ########################################################

    /**
     * @param string $title
     * @param string $color
     * @return void
     */
    private function notice(string $title, string $color): void
    {
        Notification::make()
            ->title($title)
            ->color($color)
            ->send();
    }


    /**
     * 成功時のOKボタン
     *
     * @return Action
     */
    public function successButton(): Action
    {
        // OKを押すだけのモーダルダイアログにする
        //   クローズボタンを消す
        //   クリックで閉じないようにする
        //   キャンセルボタンを消す
        return Action::make('successButton')
            ->label('完了')
            ->requiresConfirmation()
            ->modalCloseButton(false)
            ->closeModalByClickingAway(false)
            ->modalIcon('heroicon-o-check-circle')
            ->modalIconColor('success')
            ->modalCancelAction(false)
            ->modalDescription(function (array $arguments) {
                return $arguments['description'] ?? '';
            })
            ->modalSubmitActionLabel('OK');
    }

    /**
     * 失敗時のエラーボタン
     *
     * @return Action
     */
    public function errorButton(): Action
    {
        // OKを押すだけのモーダルダイアログにする
        //   クローズボタンを消す
        //   クリックで閉じないようにする
        //   キャンセルボタンを消す
        return Action::make('errorButton')
            ->label('エラー')
            ->requiresConfirmation()
            ->modalCloseButton(false)
            ->closeModalByClickingAway(false)
            ->modalIcon('heroicon-o-x-circle')
            ->modalIconColor('danger')
            ->modalDescription(function (array $arguments) {
                return $arguments['description'] ?? '';
            })
            ->modalContentFooter(function (array $arguments) {
                // modalDescriptionでは改行ができないので、modalContentFooterを使って分けて表示する
                $errorMessage = $arguments['error'] ?? '';
                $view = view('livewire.common-error', [
                    'errorMessage' => $errorMessage,
                ]);
                return $view;
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('閉じる');
    }
}
