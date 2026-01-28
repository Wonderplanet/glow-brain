<?php

declare(strict_types=1);

namespace App\Filament\Resources\CollectCurrencyPaidResource\Pages;

use App\Constants\Database;
use App\Constants\SystemConstants;
use App\Filament\Resources\CollectCurrencyPaidResource;
use App\Infolists\Components\DateTimeEntry;
use App\Models\Usr\UsrStoreProductHistory;
use App\Traits\DatabaseTransactionTrait;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use WonderPlanet\Domain\Billing\Delegators\BillingAdminDelegator;
use WonderPlanet\Domain\Currency\Entities\CollectPaidCurrencyAdminTrigger;

/**
 * 有償一次通貨回収ツール - 回収データの詳細表示と回収ページの実装
 */
class DetailCollectCurrencyPaid extends Page
{
    use InteractsWithFormActions;
    use DatabaseTransactionTrait;

    private const DEVICE_ID = 'collect by tool';
    private const RECEIPT_BUNDLE_ID = 'COLLECT_BY_TOOL';

    protected static string $resource = CollectCurrencyPaidResource::class;

    protected static string $view = 'filament.resources.collect-currency-paid.detail-collect-currency-paid';

    protected static ?string $title = '詳細';

    private const RECEIPT_UNIQUE_ID_PREFIX = 'COLLECT_BY_TOOL';

    // GETで送信されてくるパラメータ
    public string $historyId = '';
    public string $userId = '';

    /**
     * GETパラメータを受け取るLivewireの設定
     *
     * @var array
     */
    protected $queryString = [
        'historyId',
        'userId',
    ];

    // 返却する時に追加されるパラメータ
    public string $comment = '';

    // 処理対象になるレコード
    private ?Collection $usr = null;

    // 情報表示可否フラグ
    //  falseだった場合は回収ボタンを押せない
    public bool $isViewInfoList = false;

    /**
     * 対象にする情報表示
     *
     * @param Infolist $infolist
     * @return Infolist
     */
    public function infoList(Infolist $infoList): Infolist
    {
        $this->validate([
            'historyId' => 'required|string',
            'userId' => 'required|string',
        ]);

        // ユーザーのショップ購入履歴からデータを取得
        $query = UsrStoreProductHistory::query()
            ->select(
                'id',
                'receipt_unique_id',
                'os_platform',
                'usr_user_id',
                'device_id',
                'age',
                'product_sub_id',
                'platform_product_id',
                'mst_store_product_id',
                'currency_code',
                'receipt_bundle_id',
                'paid_amount',
                'free_amount',
                'purchase_price',
                'price_per_amount',
                'vip_point',
                'is_sandbox',
                'billing_platform',
                'created_at',
                'updated_at',
            );
        $usrStoreProductHistory = $query
            ->where('id', $this->historyId)
            ->where('usr_user_id', $this->userId)
            ->first();

        if (is_null($usrStoreProductHistory)) {
            return $infoList;
        }

        $this->isViewInfoList = true;

        return $infoList
            ->state([
                'usr_user_id' => $usrStoreProductHistory->usr_user_id,
                'created_at' => $usrStoreProductHistory->created_at,
                'receipt_unique_id' => $usrStoreProductHistory->receipt_unique_id,
                'os_platform' => $usrStoreProductHistory->os_platform,
                'device_id' => $usrStoreProductHistory->device_id,
                'age' => $usrStoreProductHistory->age,
                'product_sub_id' => $usrStoreProductHistory->product_sub_id,
                'platform_product_id' => $usrStoreProductHistory->platform_product_id,
                'mst_store_product_id' => $usrStoreProductHistory->mst_store_product_id,
                'currency_code' => $usrStoreProductHistory->currency_code,
                'receipt_bundle_id' => $usrStoreProductHistory->receipt_bundle_id,
                'paid_amount' => $usrStoreProductHistory->paid_amount,
                'free_amount' => $usrStoreProductHistory->free_amount,
                'purchase_price' => $usrStoreProductHistory->purchase_price,
                'price_per_amount' => $usrStoreProductHistory->price_per_amount,
                'vip_point' => $usrStoreProductHistory->vip_point,
                'is_sandbox' => $usrStoreProductHistory->is_sandbox ? 'はい' : 'いいえ',
                'billing_platform' => $usrStoreProductHistory->billing_platform,

            ])
            ->schema([
                Fieldset::make('detail')
                    ->label('詳細')
                    ->schema([
                        TextEntry::make('usr_user_id')
                            ->label('ユーザーID'),
                        DateTimeEntry::make('created_at')
                            ->label('商品購入日時'),
                        TextEntry::make('receipt_unique_id')
                            ->label('レシートユニークID'),
                        TextEntry::make('receipt_bundle_id')
                            ->label('レシートバンドルID'),
                        TextEntry::make('os_platform')
                            ->label('OSプラットフォーム'),
                        TextEntry::make('billing_platform')
                            ->label('課金プラットフォーム'),
                        TextEntry::make('device_id')
                            ->label('購入時の端末情報'),
                        TextEntry::make('age')
                            ->label('年齢'),
                        TextEntry::make('product_sub_id')
                            ->label('プロダクトサブID'),
                        TextEntry::make('platform_product_id')
                            ->label('PFプロダクトID'),
                        TextEntry::make('mst_store_product_id')
                            ->label('マスタープロダクトID'),
                        TextEntry::make('currency_code')
                            ->label('通貨コード'),
                        TextEntry::make('purchase_price')
                            ->label('購入価格'),
                        TextEntry::make('price_per_amount')
                            ->label('単価'),
                        TextEntry::make('paid_amount')
                            ->label('有償一次通貨の付与量'),
                        TextEntry::make('free_amount')
                            ->label('無償一次通貨の付与量'),
                        TextEntry::make('vip_point')
                            ->label('購入時に獲得したVIPポイント'),
                        TextEntry::make('is_sandbox')
                            ->label('サンドボックスデータ可否'),
                    ]),
            ]);
    }

    /**
     * 返却実行フォーム
     *
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form->schema(
            [
                Textarea::make('comment')
                    ->label('コメント'),
            ]
        );
    }

    /**
     * 検索用フォームで使用するアクションボタンを返す
     *
     * @return array
     */
    public function getFormActions(): array
    {
        return [
            $this->cancelButton(),
            $this->submitButton(),
        ];
    }

    /**
     * 実行ボタン
     *
     * @return Action
     */
    public function submitButton(): Action
    {
        return Action::make('submitButton')
            ->label('有償一次通貨を回収')
            ->requiresConfirmation()
            ->disabled(fn () => !$this->isViewInfoList)
            ->action(fn () => $this->collectCurrencyPaid());
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
            ->modalDescription('有償一次通貨の回収が完了しました')
            ->modalSubmitActionLabel('OK')
            ->action(function () {
                // リストに戻す
                // submitのアクションが実行されるので、Acton::urlは反応しない。
                // そのためredirect()->toを使う
                redirect()->to(self::getResource()::getUrl('index', ['userId' => $this->userId]));
            });
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
            ->modalDescription(function () {
                return '有償一次通貨の回収に失敗しました';
            })
            ->modalContentFooter(function (array $arguments) {
                // modalDescriptionでは改行ができないので、modalContentFooterを使って分けて表示する
                $errorMessage = $arguments[0] ?? '';
                return view('livewire.log-currency-revert.detail-log-currency-revert-error', [
                    'errorMessage' => $errorMessage,
                ]);
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('閉じる');
    }

    /**
     * キャンセルボタン
     * 一覧に戻す
     *
     * @return Action
     */
    public function cancelButton(): Action
    {
        return Action::make('cancelButton')
            ->label('キャンセル')
            ->color('gray')
            ->url(self::getResource()::getUrl('index', ['userId' => $this->userId]));
    }

    /**
     * メッセージの通知を行う
     *
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
     * 有償一次通貨回収を実行
     *
     * @return void
     * @throws \Throwable
     */
    public function collectCurrencyPaid(): void
    {
        try {
            // トランザクションの開始
            $this->transaction(function () {
                // delegatorを取得
                /** @var BillingAdminDelegator $billingAdminService */
                $billingAdminDelegator = app()->make(BillingAdminDelegator::class);

                // $receiptUniqueIdを作成
                $receiptUniqueId = $this->makeReceiptUniqueId();

                // triggerIdには回収したusrStoreProductHistoryのidを記録する
                $trigger = new CollectPaidCurrencyAdminTrigger(
                    $this->historyId,
                    $this->comment
                );

                // 有償一次通貨回収を実行
                $billingAdminDelegator->returnedPurchase(
                    $this->userId,
                    $this->historyId,
                    self::DEVICE_ID,
                    self::RECEIPT_BUNDLE_ID,
                    $receiptUniqueId,
                    $trigger
                );
            }, [Database::TIDB_CONNECTION]);

            // 成功通知をする
            $this->notice('有償一次通貨の回収が完了しました', 'success');

            // 完了ダイアログを出す
            $this->replaceMountedAction('successButton');
        } catch (\Exception $e) {
            Log::error('', [$e]);
            $this->notice('有償一次通貨の回収に失敗しました', 'danger');

            // 失敗ダイアログを出す
            $arguments = [$e->getMessage()];
            $this->replaceMountedAction('errorButton', $arguments);
        }
    }

    /**
     * ダミー用のreceiptUniqueIdを生成
     *
     * @return string
     */
    private function makeReceiptUniqueId(): string
    {
        $now = \Illuminate\Support\Carbon::now();
        $nowJst = $now->clone()->setTimezone(SystemConstants::FORM_INPUT_TIMEZONE);
        return self::RECEIPT_UNIQUE_ID_PREFIX
            . '_' . $nowJst->format('Y-m-d H:i:s')
            . '_' . Uuid::uuid4()->toString();
    }
}
