<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Constants\Database;
use App\Constants\NavigationGroups;
use App\Domain\Currency\Utils\CurrencyUtility;
use App\Exceptions\UsrUserCurrencyConsumeException;
use App\Filament\Authorizable;
use App\Models\Usr\UsrUser;
use App\Traits\DatabaseTransactionTrait;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyAdminDelegator;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity;

class UsrUserCurrencyPaidConsume extends Page
{
    use Authorizable;
    use DatabaseTransactionTrait;
    use InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.usr-user-currency-paid-consume';

    protected static ?string $navigationGroup = NavigationGroups::DEBUG->value;
    protected static ?string $title = '一次通貨消費';

    public ?string $userId = '';
    public array $userData = [];
    public string $currencyType = '';
    public string $billingPlatform = '';
    public int $consumeAmount = 1;
    public string $triggerType = '';
    public string $triggerId = '';
    public string $triggerName = '';

    public const USE_CURRENCY = 'use_currency';
    public const USE_PAID = 'use_paid';

    /**
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('userId')
                    ->required()
                    ->label('対象ユーザーID')
                    ->placeholder('ユーザーIDを入力')
                    ->columnSpanFull()
                    ->reactive()
                    ->afterStateUpdated(function ($set, $state) {
                        // 入力ユーザーの所持通貨データを取得する
                        $userData = $this->getUserData($state);
                        $set('userData', $userData);
                    }),
                Forms\Components\Select::make('currencyType')
                    ->options([
                        self::USE_CURRENCY => '無償 + 有償から消費する',
                        self::USE_PAID => '有償のみ消費する',
                    ])
                    ->required()
                    ->label('消費通貨の無償・有償の指定')
                    ->placeholder('下記から選択してください')
                    ->columnSpanFull()
                    ->reactive(),
                Forms\Components\Select::make('billingPlatform')
                    ->options([
                        CurrencyConstants::PLATFORM_APPSTORE => CurrencyConstants::PLATFORM_APPSTORE . ' - ' . CurrencyConstants::OS_PLATFORM_IOS,
                        CurrencyConstants::PLATFORM_GOOGLEPLAY => CurrencyConstants::PLATFORM_GOOGLEPLAY . ' - ' . CurrencyConstants::OS_PLATFORM_ANDROID
                    ])
                    ->required()
                    ->label('消費通貨のプラットフォーム指定')
                    ->placeholder('下記から選択してください')
                    ->columnSpanFull()
                    ->reactive(),
                Forms\Components\TextInput::make('consumeAmount')
                    ->required()
                    ->label('消費数')
                    ->placeholder('個数を入力')
                    ->columnSpanFull()
                    ->numeric()
                    ->reactive(),
                Forms\Components\TextInput::make('triggerType')
                    ->required()
                    ->label('消費理由')
                    ->hint('ガチャ、課金など')
                    ->placeholder('ガチャ、課金などを入力')
                    ->columnSpanFull()
                    ->reactive(),
                Forms\Components\TextInput::make('triggerId')
                    ->required()
                    ->label('消費理由に関連するID')
                    ->hint('ガチャID、product_sub_idなど')
                    ->placeholder('ガチャID、product_sub_idなどを入力')
                    ->columnSpanFull()
                    ->reactive(),
                Forms\Components\TextInput::make('triggerName')
                    ->required()
                    ->label('消費理由に関連する名前')
                    ->hint('ガチャ名、課金した商品名など')
                    ->placeholder('ガチャ名、商品名などを入力')
                    ->columnSpanFull()
                    ->reactive(),
            ]);
    }

    /**
     * フォームで使用するアクションボタンを返す
     *
     * @return array<Action>
     */
    public function getFormActions(): array
    {
        return [
            $this->consumeButton(),
        ];
    }

    /**
     * @return Action
     */
    private function consumeButton(): Action
    {
        return Action::make('consumeButton')
            ->label('消費')
            ->requiresConfirmation()
            ->disabled(function () {
                return empty($this->userId)
                    || empty($this->currencyType)
                    || empty($this->billingPlatform)
                    || empty($this->consumeAmount)
                    || empty($this->triggerType)
                    || empty($this->triggerId)
                    || empty($this->triggerName);
            })
            ->action(fn () => $this->consumeCurrency());
    }

    /**
     * 入力されたユーザーIDの所持通貨情報を取得
     *
     * @param string $userId
     * @return array<int, array<string, string>>
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getUserData(string $userId): array
    {
        /** @var CurrencyAdminDelegator $currencyAdminDelegator */
        $currencyAdminDelegator = app()->make(CurrencyAdminDelegator::class);

        /** @var UsrCurrencySummaryEntity|null $summary */
        $summary = $currencyAdminDelegator->getCurrencySummary($userId);

        if (is_null($summary)) {
            // 情報がなければ空配列を返す
            return [];
        }

        return [
            'userId' => $userId,
            'totalAmount' => $summary->getTotalAmount(),
            'paidAmount' => $summary->getTotalPaidAmount(),
            'paidAmountApple' => $summary->getPaidAmountApple(),
            'paidAmountGoogle' => $summary->getPaidAmountGoogle(),
            'freeAmount' => $summary->getFreeAmount(),
        ];
    }

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
     * 有償一次通貨消費処理
     *
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function consumeCurrency(): void
    {
        // 入力値のバリデーション
        $this->validate();

        $usr = UsrUser::query()->where('id', $this->userId)->get(['id']);

        if ($usr->count() !== 1) {
            // 存在しないユーザーIDが入力されてないかチェック
            $this->notice('存在しないユーザーIDが入力されています', 'danger');
            return;
        }

        if (empty($this->userData)) {
            $this->notice('通貨情報が確認できません', 'danger');
            return;
        }

        // 所持通貨量と消費数のチェック
        //  無償通貨と消費対象の有償通貨を取得
        $freeAmount = $this->userData['freeAmount'];
        $paidAmount = $this->billingPlatform === CurrencyConstants::PLATFORM_APPSTORE
            ? $this->userData['paidAmountApple']
            : $this->userData['paidAmountGoogle'];
        //  チェックする所持総数を取得
        //   有償のみ消費するなら$paidAmountのみ
        //   無償+有償から消費するなら$paidAmountと$freeAmountを合算
        $checkAmount = $this->currencyType === self::USE_PAID
            ? (int) $paidAmount
            : (int) $paidAmount + (int) $freeAmount;
        if ($checkAmount < $this->consumeAmount) {
            $msg = $this->currencyType === self::USE_PAID
                ? "{$this->billingPlatform}購入通貨量"
                : "{$this->billingPlatform}と無償通貨の合計量";
            $this->notice("{$msg}を超えた消費数が入力されています", 'danger');
            return;
        }

        try {
            $this->transaction(
                function () {
                    try {
                        /** @var CurrencyAdminDelegator $currencyAdminDelegator */
                        $currencyAdminDelegator = app()->make(CurrencyAdminDelegator::class);
                        $osPlatform = CurrencyUtility::getOsPlatformByBillingPlatform($this->billingPlatform);

                        // デバッグ用のTrigger生成
                        $trigger = new Trigger(
                            $this->triggerType,
                            $this->triggerId,
                            $this->triggerName,
                            'debugConsume'
                        );

                        switch ($this->currencyType) {
                            case self::USE_CURRENCY:
                                // 無償 > 有償の順で消費する
                                $currencyAdminDelegator->useCurrency(
                                    $this->userId,
                                    $osPlatform,
                                    $this->billingPlatform,
                                    $this->consumeAmount,
                                    $trigger
                                );
                                break;
                            case self::USE_PAID:
                                // 有償のみで消費する
                                $currencyAdminDelegator->usePaid(
                                    $this->userId,
                                    $osPlatform,
                                    $this->billingPlatform,
                                    $this->consumeAmount,
                                    $trigger
                                );
                                break;
                            default:
                                throw new \Exception("currencyTypeが不正 currencyType={$this->currencyType}");
                        }
                    } catch (\Exception $e) {
                        Log::error('', [$e]);
                        throw new UsrUserCurrencyConsumeException('UserCurrencyConsumeError');
                    }
                },
                [
                    Database::TIDB_CONNECTION
                ]
            );

            $this->notice('一次通貨消費が完了しました', 'success');
            $this->userData = $this->getUserData($this->userId);
        } catch (\Exception $e) {
            Log::error('', [$e]);
            $this->notice('一次通貨消費に失敗しました', 'danger');
        }
    }
}
