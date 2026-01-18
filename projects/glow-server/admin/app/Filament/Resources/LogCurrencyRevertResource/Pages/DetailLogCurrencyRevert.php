<?php

declare(strict_types=1);

namespace App\Filament\Resources\LogCurrencyRevertResource\Pages;

use App\Constants\Database;
use App\Filament\Resources\LogCurrencyRevertResource;
use App\Models\Log\LogCurrencyFree;
use App\Models\Log\LogCurrencyPaid;
use App\Models\Log\LogCurrencyUnionModel;
use App\Traits\DatabaseTransactionTrait;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Currency\Delegators\CurrencyAdminDelegator;

/**
 * 一次通貨の返却内容の詳細を確認するページ
 *
 * モーダルダイアログで表示したかったが方法がみつからなかったのでページ遷移させている
 * 複数のレコードを同時に扱う必要があるため、ひとつのIDで処理できないため、Actionでは操作できなかった
 *
 * そのためグルーピングした条件をページ遷移先に飛ばして、そこで条件にあうレコードを検索して表示するようにしている
 *
 * レコード作成の挙動をさせるためCreateRecordを継承している
 */
class DetailLogCurrencyRevert extends Page
{
    use DatabaseTransactionTrait;

    protected static string $resource = LogCurrencyRevertResource::class;

    protected static string $view = 'filament.resources.log-currency-revert.detail-log-currency-revert';

    protected static ?string $title = '詳細';

    // GETで送信されてくるパラメータ
    public string $userId = '';
    public string $triggerType = '';
    public string $triggerId = '';
    public string $triggerName = '';
    public string $requestId = '';
    public string $createdAt = '';
    public string $searchStartDate = '';
    public string $searchEndDate = '';

    /**
     * GETパラメータを受け取るLivewireの設定
     *
     * @var array
     */
    protected $queryString = [
        'userId',
        'triggerType',
        'triggerId',
        'triggerName',
        'requestId',
        'createdAt',
        'searchStartDate',
        'searchEndDate',
    ];

    // 返却する時に追加されるパラメータ
    public string $comment = '';
    public array $logPaidIds = [];
    public array $logFreeIds = [];

    // 処理対象になるレコード
    private ?Collection $currencyLogRecords = null;

    /**
     * 処理する通貨ログレコードを取得する
     *
     * @return Collection
     */
    private function getCurrencyLogRecords(): Collection
    {
        if (is_null($this->currencyLogRecords)) {
            // user_idもここに含められている
            $query = $this->getUnionQuery();

            // 消費のみを対象とするため、変動した一次通貨の合計値がマイナスのもののみ表示する
            $query->where('log_change_amount', '<', 0);

            // 有償・無償一次通貨ログから条件にあうものを検索
            $this->currencyLogRecords = $query
                ->where('trigger_type', $this->triggerType)
                ->where('trigger_id', $this->triggerId)
                ->where('trigger_name', $this->triggerName)
                ->where('request_id', $this->requestId)
                ->where('created_at', $this->createdAt)
                ->get();
        }

        return $this->currencyLogRecords;
    }

    /**
     * 対象にする情報表示
     *
     * @param Infolist $infolist
     * @return Infolist
     */
    public function infoList(Infolist $infolist): Infolist
    {
        /**
         * 受け取ったGETパラメータで情報を表示するためのinfolist構築
         *
         * livewireのバリデーションルールを使うためにvalidateを使う
         * formから呼ばれるメソッドで行うとエラーになるため、ここで行う
         */
        $this->validate([
            'userId' => 'required|string',
            'triggerType' => 'required|string',
            'triggerId' => 'string',
            'triggerName' => 'string',
            'requestId' => 'required|string',
            'createdAt' => 'required|string',
        ]);

        // 有償・無償一次通貨ログから条件にあうものを検索
        // IDを仕分け
        $logPaidIds = $this->getCurrencyLogRecords()->where('log_currency_type', 'paid')->pluck('id')->toArray();
        $logFreeIds = $this->getCurrencyLogRecords()->where('log_currency_type', 'free')->pluck('id')->toArray();
        // レコード取得
        $logPaids = LogCurrencyPaid::query()
            ->whereIn('id', $logPaidIds)
            ->get();
        $logFrees = LogCurrencyFree::query()
            ->whereIn('id', $logFreeIds)
            ->get();

        return $infolist
            ->state([
                'userId' => $this->userId,
                'triggerType' => $this->triggerType,
                'triggerId' => $this->triggerId,
                'triggerName' => $this->triggerName,
                'requestId' => $this->requestId,
                'createdAt' => $this->createdAt,
                'logPaids' => $logPaids,
                'logFrees' => $logFrees,
            ])
            ->schema([
                Fieldset::make('detail')
                    ->label('詳細')
                    ->schema([
                        TextEntry::make('userId')
                            ->label('ユーザーID'),
                        TextEntry::make('triggerType')
                            ->label('トリガータイプ'),
                        TextEntry::make('triggerId')
                            ->label('トリガーID'),
                        TextEntry::make('triggerName')
                            ->label('トリガー名'),
                        TextEntry::make('requestId')
                            ->label('リクエストID'),
                        TextEntry::make('createdAt')
                            ->label('作成日時'),
                    ]),
                RepeatableEntry::make('logPaids')
                    ->label('有償一次通貨返却対象ログ')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID')
                            ->columnSpanFull(),
                        // 単価
                        TextEntry::make('price_per_amount')
                            ->label('単価'),
                        // 個数
                        TextEntry::make('change_amount')
                            ->label('個数'),
                    ])
                    ->columns(3),
                RepeatableEntry::make('logFrees')
                    ->label('無償一次通貨返却対象ログ')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID')
                            ->columnSpanFull(),
                        // ゲーム内通貨
                        TextEntry::make('change_ingame_amount')
                            ->label('ゲーム内通貨'),
                        // ボーナス通貨
                        TextEntry::make('change_bonus_amount')
                            ->label('ボーナス通貨'),
                        // リワード通貨
                        TextEntry::make('change_reward_amount')
                            ->label('リワード通貨'),
                    ])
                    ->columns(3),
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
        // 返却対象のIDを抽出して埋め込む
        $this->logPaidIds = $this->getCurrencyLogRecords()->where('log_currency_type', 'paid')->pluck('id')->toArray();
        $this->logFreeIds = $this->getCurrencyLogRecords()->where('log_currency_type', 'free')->pluck('id')->toArray();

        return $form->schema(
            [
                Hidden::make('logPaidIds'),
                Hidden::make('logFreeIds'),
                Textarea::make('comment')
                    ->label('コメント'),
            ]
        );
    }

    /**
     * 実行ボタン
     * メソッド名とmmakeの引数は一致している必要がある
     *
     * @return Action
     */
    public function submitButton(): Action
    {
        return Action::make('submitButton')
            ->label('一次通貨を返却')
            ->requiresConfirmation()
            ->action(function () {
                try {
                    // トランザクションの開始
                    $this->transaction(function () {
                        // delegatorを取得
                        $currencyAdminDelegator = app()->make(CurrencyAdminDelegator::class);

                        // 一次通貨の返却を実行
                        $currencyAdminDelegator->revertCurrencyFromLog(
                            $this->userId,
                            $this->logPaidIds,
                            $this->logFreeIds,
                            $this->comment
                        );
                    }, [Database::TIDB_CONNECTION]);

                    // 成功通知をする
                    $this->notice('一次通貨の返却が完了しました', 'success');

                    // 完了ダイアログを出す
                    $this->replaceMountedAction('successButton');
                } catch (\Exception $e) {
                    Log::error('', [$e]);
                    $this->notice('一次通貨の返却に失敗しました', 'danger');

                    // 失敗ダイアログを出す
                    $arguments = [$e->getMessage()];
                    $this->replaceMountedAction('errorButton', $arguments);
                }
            });
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
            ->modalDescription('一次通貨の返却が完了しました')
            ->modalSubmitActionLabel('OK')
            ->action(function () {
                // リストに戻す
                redirect()->route('filament.admin.resources.log-currency-reverts.index');
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
                return '一次通貨の返却に失敗しました';
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
            ->action(function () {
                // リストに戻す
                return redirect()->route('filament.admin.resources.log-currency-reverts.index', [
                    'userId' => $this->userId,
                    'startDate' => $this->searchStartDate,
                    'endDate' => $this->searchEndDate,
                ]);
            });
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
     * 返却処理の対象になるログ取得用のクエリを作成する
     * freeとpaidを結合する必要があるのでUNIONしたものを返す
     *
     * @return Builder
     */
    private function getUnionQuery(): Builder
    {
        // 有償一次通貨と無償一次通貨のログを結合して表示する
        $paidQuery = LogCurrencyPaid::query()
            ->select(
                'id',
                'seq_no',
                'usr_user_id',
                'currency_paid_id',
                'receipt_unique_id',
                'is_sandbox',
                'query',
                'purchase_price',
                'purchase_amount',
                'price_per_amount',
                'currency_code',
                'before_amount',
                'change_amount',
                'current_amount',
                'os_platform',
                'billing_platform',
                'trigger_type',
                'trigger_id',
                'trigger_name',
                'trigger_detail',
                'request_id',
                'created_at',
                'updated_at',
                // 無償一次通貨のログと結合するためのカラム
                DB::raw('0 as `before_ingame_amount`'),
                DB::raw('0 as `before_bonus_amount`'),
                DB::raw('0 as `before_reward_amount`'),
                DB::raw('0 as `change_ingame_amount`'),
                DB::raw('0 as `change_bonus_amount`'),
                DB::raw('0 as `change_reward_amount`'),
                DB::raw('0 as `current_ingame_amount`'),
                DB::raw('0 as `current_bonus_amount`'),
                DB::raw('0 as `current_reward_amount`'),
                // UNIONしたテーブルを区別するための区分
                DB::raw('\'paid\' as `log_currency_type`'),
                // 変動した一次通貨の合計値
                DB::raw('`change_amount` as `log_change_amount`'),
                // 変動した有償一次通貨の合計値
                DB::raw('`change_amount` as `log_change_amount_paid`'),
                // 変動した無償一次通貨の合計値
                DB::raw('0 as `log_change_amount_free`'),
            );
        // 有償一次通貨のログと結合するためのカラムを追加
        $freeQuery = LogCurrencyFree::query()
            ->select(
                'id',
                DB::raw('0 as `seq_no`'),
                'usr_user_id',
                DB::raw('\'\' as `currency_paid_id`'),
                DB::raw('\'\' as `receipt_unique_id`'),
                DB::raw('0 as `is_sandbox`'),
                DB::raw('\'\' as `query`'),
                DB::raw('0 as `purchase_price`'),
                DB::raw('0 as `purchase_amount`'),
                DB::raw('0 as `price_per_amount`'),
                DB::raw('\'\' as `currency_code`'),
                DB::raw('0 as `before_amount`'),
                DB::raw('0 as `change_amount`'),
                DB::raw('0 as `current_amount`'),
                'os_platform',
                DB::raw('\'\' as `billing_platform`'),
                'trigger_type',
                'trigger_id',
                'trigger_name',
                'trigger_detail',
                'request_id',
                'created_at',
                'updated_at',
                'before_ingame_amount',
                'before_bonus_amount',
                'before_reward_amount',
                'change_ingame_amount',
                'change_bonus_amount',
                'change_reward_amount',
                'current_ingame_amount',
                'current_bonus_amount',
                'current_reward_amount',
                // UNIONしたテーブルを区別するための区分
                DB::raw('\'free\' as `log_currency_type`'),
                // 変動した一次通貨の合計値
                DB::raw('`change_ingame_amount` + `change_bonus_amount` + `change_reward_amount` as `log_change_amount`'),
                // 変動した有償一次通貨の合計値
                DB::raw('0 as `log_change_amount_paid`'),
                // 変動した無償一次通貨の合計値
                DB::raw('`change_ingame_amount` + `change_bonus_amount` + `change_reward_amount` as `log_change_amount_free`'),
            );
        $paidQuery = $this->buildQuery($paidQuery);
        $freeQuery = $this->buildQuery($freeQuery);

        // paidとfreeをunionした結果を検索クエリとして設定する
        $paidQuery->union($freeQuery);
        $query = LogCurrencyUnionModel::query()
            ->fromSub($paidQuery, 'log');

        return $query;
    }

    /**
     * 現在のパラメータを元にクエリを作成する
     *
     * @param Builder $query
     * @return Builder
     */
    private function buildQuery(Builder $query): Builder
    {
        // user_idは必須
        $query->where('usr_user_id', $this->userId);

        return $query;
    }
}
