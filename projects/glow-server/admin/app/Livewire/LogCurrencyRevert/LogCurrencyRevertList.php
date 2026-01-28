<?php

declare(strict_types=1);

namespace App\Livewire\LogCurrencyRevert;

use App\Filament\Resources\LogCurrencyRevertResource;
use App\Models\Log\LogCurrencyFree;
use App\Models\Log\LogCurrencyPaid;
use App\Models\Log\LogCurrencyUnionModel;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * 検索条件に合致する有償・無償一次通貨返却対象の履歴を表示する
 */
class LogCurrencyRevertList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    /**
     * dispatchで受け取るlistener
     *
     * @var array
     */
    protected $listeners = [
        'searchUpdated' => 'onSearchUpdated',
    ];

    /**
     * ユーザーID検索条件
     *
     * @var string|null
     */
    public ?string $userId = '';

    /**
     * 開始日の検索条件
     *
     * @var string|null
     */
    public ?string $startDate = '';

    /**
     * 終了日の検索条件
     *
     * @var string|null
     */
    public ?string $endDate = '';

    /**
     * ページのレンダリング
     *
     * @return void
     */
    public function render()
    {
        return view('livewire.log-currency-revert.log-currency-revert-list');
    }

    /**
     * テーブル表示情報を作成
     *
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        // テーブル表示の検索条件
        // 有償一次通貨と無償一次通貨の消費履歴を表示する

        // log_currency_paidから検索
        $query = $this->getUnionQuery();

        // 消費のみを対象とするため、変動した一次通貨の合計値がマイナスのもののみ表示する
        $query->where('log_change_amount', '<', 0);

        // 消費したタイミングごとに集計する
        $groupByColumns = ['usr_user_id', 'trigger_type', 'trigger_id', 'trigger_name', 'created_at', 'request_id'];
        $query->groupBy($groupByColumns);

        // 表示用の情報
        //   idの項目がないとエラーになるため、適当なUUIDを生成している
        $query->select($groupByColumns);
        $query->selectRaw('UUID() as `id`');
        $query->selectRaw('sum(log_change_amount_paid) as sum_log_change_amount_paid');
        $query->selectRaw('sum(log_change_amount_free) as sum_log_change_amount_free');

        return $table->query($query)
            ->columns([
                // ユーザーID
                TextColumn::make('usr_user_id')
                    ->label('ユーザーID')
                    ->sortable()
                    ->alignCenter(),

                // コンテンツ消費日時
                TextColumn::make('created_at')
                    ->label('コンテンツ消費日時')->alignCenter()
                    ->sortable(),

                // 消費コンテンツ名
                TextColumn::make('trigger_name')
                    ->label('消費コンテンツ名')
                    ->getStateUsing(function (Model $record): string {
                        // 名前とIDを表示
                        return $record->trigger_type . '/' . $record->trigger_id . ': ' . $record->trigger_name;
                    })
                    ->alignCenter(),

                // 消費有償一次通貨数（合計）
                TextColumn::make('sum_log_change_amount_paid')
                    ->label('消費有償一次通貨数(合計)')->alignCenter(),
                // 消費無償一次通貨数
                TextColumn::make('sum_log_change_amount_free')
                    ->label('消費無償一次通貨数(合計)')->alignCenter(),

            ])->actions([
                // グルーピングした情報を元に詳細を表示する
                Action::make('detail')
                    ->label('返却')
                    ->icon('heroicon-o-plus-circle')
                    ->url(function (Model $record) {
                        $route = LogCurrencyRevertResource::getUrl('detail', [
                            'userId' => $record->usr_user_id,
                            'triggerType' => $record->trigger_type,
                            'triggerId' => $record->trigger_id,
                            'triggerName' => $record->trigger_name,
                            'createdAt' => (string)$record->created_at,
                            'requestId' => $record->request_id,
                            'searchStartDate' => $this->startDate,
                            'searchEndDate' => $this->endDate,
                        ]);
                        return $route;
                    }),
            ], position: ActionsPosition::BeforeColumns)
            ->defaultSort('created_at', 'desc');
    }

    /**
     * 検索条件を更新する
     *
     * @param string $userId
     * @param string $startDate
     * @param string $endDate
     * @return void
     */
    public function onSearchUpdated(string $userId, string $startDate, string $endDate)
    {
        $this->userId = $userId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        $table = $this->getTable();
        $this->table($table);
    }

    /**
     * テーブルに表示するためのクエリを取得する
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
        // user_idがない場合は空振りになるので、そのまま返す
        $query->where('usr_user_id', $this->userId);

        if ($this->startDate !== '') {
            $query->where('created_at', '>=', $this->startDate);
        }
        if ($this->endDate !== '') {
            $query->where('created_at', '<=', $this->endDate);
        }

        return $query;
    }
}
