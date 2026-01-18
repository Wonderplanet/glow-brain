<?php

namespace App\Filament\Pages;

use App\Constants\Database;
use App\Constants\NavigationGroups;
use App\Domain\Currency\Utils\CurrencyUtility;
use App\Exceptions\UsrUserCurrencyPaidAddException;
use App\Filament\Authorizable;
use App\Models\Usr\UsrCurrencySummary;
use App\Models\Usr\UsrUser;
use App\Traits\DatabaseTransactionTrait;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyAdminDelegator;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity;

class UsrUserCurrencyPaidAdd extends Page
{
    use Authorizable;
    use DatabaseTransactionTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.usr-user-currency-paid-add';

    protected static ?string $navigationGroup = NavigationGroups::DEBUG->value;
    protected static ?string $title = '有償一次通貨付与';

    public ?string $userIdsString = '';
    public ?string $billingPlatform = '';
    public ?string $currencyCode = 'JPY';
    public ?string $purchasePrice = '';
    public int $purchaseAmount = 1;
    public array $userData = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('userIdsString')
                    ->required()
                    ->label('対象ユーザーID')
                    ->hint('複数人に付与する場合は改行もしくはカンマ区切りで入力(最大10人)')
                    ->placeholder('ユーザーIDを入力')
                    ->columnSpanFull()
                    ->reactive()
                    ->afterStateUpdated(function ($set, $state) {
                        $this->userData = [];
                        $userIds = $this->makeUserIds($state);
                        if (count($userIds) === 1) {
                            // 入力ユーザーが１名だけの場合は所持通貨データを取得する
                            $userId = $userIds[0];
                            $this->getOneUserData($userId);
                            $set('userData', $this->userData);
                        }
                    })
                    ->rows(10),
                Forms\Components\Select::make('billingPlatform')
                    ->options([
                        CurrencyConstants::PLATFORM_APPSTORE => CurrencyConstants::PLATFORM_APPSTORE . ' - ' . CurrencyConstants::OS_PLATFORM_IOS,
                        CurrencyConstants::PLATFORM_GOOGLEPLAY => CurrencyConstants::PLATFORM_GOOGLEPLAY . ' - ' . CurrencyConstants::OS_PLATFORM_ANDROID
                    ])
                    ->required()
                    ->label('PF')
                    ->placeholder('課金プラットフォームを選択')
                    ->columnSpanFull()
                    ->reactive(),
                Forms\Components\TextInput::make('currencyCode')
                    ->required()
                    ->label('通貨コード')
                    ->hint('JPY、USDなど')
                    ->placeholder('通貨コードを入力')
                    ->columnSpanFull()
                    ->reactive(),
                Forms\Components\TextInput::make('purchasePrice')
                    ->required()
                    ->label('購入価格')
                    ->hint('USDの場合は小数点も含めて入力してください')
                    ->placeholder('購入価格を入力')
                    ->columnSpanFull()
                    ->numeric()
                    ->reactive(),
                Forms\Components\TextInput::make('purchaseAmount')
                    ->required()
                    ->label('個数')
                    ->placeholder('個数を入力')
                    ->columnSpanFull()
                    ->numeric()
                    ->reactive(),
        ]);
    }

    public function addCurrency(): void
    {
        // 入力値のバリデーション
        $this->validate(
            [
                'userIdsString' => [
                    'required',
                ],
                'billingPlatform' => [
                    'required',
                ],
                'currencyCode' => [
                    'required',
                ],
                'purchasePrice' => [
                    'required',
                ],
                'purchaseAmount' => [
                    'required',
                ],
            ]
        );

        $userIds = $this->makeUserIds($this->userIdsString);
        if (count($userIds) > 10) {
            $this->notice('同時に11人以上には付与できません', 'danger');
            return;
        }

        $usr = UsrUser::query()->whereIn('id', $userIds)->get(['id']);

        if (count($userIds) !== $usr->count()) {
            // 存在しないユーザーIDが入力されてないかチェック
            $usrIds = $usr->pluck('id')->toArray();
            $undefinedUserIds = array_diff($userIds, $usrIds);
            $title = '存在しないユーザーIDが入力されています<br>';
            $title .= implode(',', $undefinedUserIds);

            $this->notice($title, 'danger');
            return;
        }

        try {
            $this->transaction(
                function () use (
                    $userIds,
                ) {
                    try {
                        /** @var CurrencyAdminDelegator $currencyAdminDelegator */
                        $currencyAdminDelegator = app()->make(CurrencyAdminDelegator::class);
                        $osPlatform = CurrencyUtility::getOsPlatformByBillingPlatform($this->billingPlatform);

                        $currencyDelegator = app()->make(CurrencyDelegator::class);
                        $existsUsrUserIds = UsrCurrencySummary::query()
                            ->whereIn('usr_user_id', $userIds)
                            ->pluck('usr_user_id', 'usr_user_id');

                        // デバッグ用のTrigger生成
                        $trigger = new Trigger('debugPurchased', '', '', '');

                        foreach ($userIds as $userId) {
                            // usrCurrencySummaryが存在しない場合はエラーになるので新規作成
                            $existsUsrUserId = $existsUsrUserIds->get($userId);
                            if (is_null($existsUsrUserId)) {
                                $currencyDelegator->createUser(
                                    $userId,
                                    $osPlatform,
                                    $this->billingPlatform,
                                    0,
                                );
                            }

                            // サンドボックス通貨として付与
                            // サンドボックスのVIPポイントはカウントしないし、商品購入でもないので0で固定する
                            $currencyAdminDelegator->addSandboxCurrencyPaid(
                                $userId,
                                $osPlatform,
                                $this->billingPlatform,
                                $this->currencyCode,
                                $this->purchasePrice,
                                $this->purchaseAmount,
                                0,
                                'debug_' . $userId . '_' . Carbon::now()->timestamp,
                                $trigger
                            );
                        }
                    } catch (\Exception) {
                        throw new UsrUserCurrencyPaidAddException('UserCurrencyPaidAddError');
                    }
                },
                [
                    Database::TIDB_CONNECTION
                ]
            );

            $this->notice('有償一次通貨付与が完了しました', 'success');
        } catch (\Exception $e) {
            Log::error('', [$e]);
            $this->notice('有償一次通貨付与に失敗しました', 'danger');
        }
    }

    private function makeUserIds(?string $userIdString): array
    {
        if (empty($userIdString)) {
            // 未入力の場合は空配列を返す
            return [];
        }

        // 各改行コードを全てカンマに変換してカンマ区切りに統一
        $tmpStr = str_replace(["\r\n", "\r", "\n"], ',', $userIdString);
        // カンマ区切りから配列に変換
        $userIds = explode(',', $tmpStr);

        // ユニークにして返す
        return array_unique($userIds);
    }

    private function notice(string $title, string $color): void
    {
        Notification::make()
            ->title($title)
            ->color($color)
            ->send();
    }

    public function getOneUserData(string $userId): void
    {
        /** @var CurrencyAdminDelegator $currencyAdminDelegator */
        $currencyAdminDelegator = app()->make(CurrencyAdminDelegator::class);

        /** @var UsrCurrencySummaryEntity|null $summary */
        $summary = $currencyAdminDelegator->getCurrencySummary($userId);

        if (!is_null($summary)) {
            $this->userData = [
                'userId' => $userId,
                'totalAmount' => $summary->getTotalAmount(),
                'paidAmount' => $summary->getTotalPaidAmount(),
                'freeAmount' => $summary->getFreeAmount(),
            ];
        }
    }

    public function addButton(): Action
    {
        return Action::make('addButton')
            ->label('付与')
            ->requiresConfirmation()
            ->action(fn () => $this->addCurrency());
    }
}
