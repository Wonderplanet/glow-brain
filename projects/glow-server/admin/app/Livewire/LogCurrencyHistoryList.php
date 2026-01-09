<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Filament\Actions\LogCurrencyHistoryDetailAction;
use App\Models\Log\LogCurrencyPaid;
use App\Models\Log\LogCurrencyUnionModel;
use App\Models\Log\LogStore;
use App\Models\Usr\UsrUser;
use App\Models\Usr\UsrUserProfile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Models\LogCurrencyFree;
use Illuminate\Support\Facades\Log;

class LogCurrencyHistoryList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    /**
     * 検索結果表示フラグ
     *
     * trueの場合にリスト表示する
     * @var boolean
     */
    public bool $enableList = false;

    // 課金ID検索
    public string $orderId = '';

    // その他の検索条件
    public string $userId = '';
    public string $userName = '';
    public string $triggerId = '';
    public string $triggerName = '';
    public string $startDate = '';
    public string $endDate = '';
    public string $minVipPoint = '';
    public string $maxVipPoint = '';

    protected $listeners = [
        'orderIdUpdated' => 'onOrderIdUpdated',
        'searchUpdated' => 'onSearchUpdated',
    ];

    public function render()
    {
        return view('livewire.log-currency-history-list');
    }

    public function table(Table $table): Table
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
                'vip_point',
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
                DB::raw('0 as `vip_point`'),
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
            );
        $paidQuery = $this->buildQuery($paidQuery, true);
        $freeQuery = $this->buildQuery($freeQuery, false);

        // 年齢認証の追加
        $paidQuery->addSelect(['age' =>
            LogStore::query()->select('age')
            ->whereColumn('usr_user_id', 'log_currency_paids.usr_user_id')
            ->whereColumn('receipt_unique_id', 'log_currency_paids.receipt_unique_id')
        ]);
        $freeQuery->addSelect(DB::raw('NULL as `age`'));

        // paidとfreeをunionした結果を検索クエリとして設定する
        $paidQuery->union($freeQuery);
        // 空のモデルからクエリを作成して、unionしたクエリをサブクエリとして設定する
        // unionはbind情報を別のキーとして持っているからmergeBindingしなくていい
        // getBindingsで取得すると全部拾ってくるので、
        //   mergeBindingすると、後のフィルタで差し込まれた値と順番が前後して動作がおかしくなる
        $eloquentBuilderQuery = LogCurrencyUnionModel::query()
            ->fromSub($paidQuery, 'log')
            ->with('user');

        return $table
            ->query($eloquentBuilderQuery)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('usr_user_id')->label('ユーザーID'),
                TextColumn::make('user.usr_user_profiles.name')->label('ユーザー名'),
                TextColumn::make('receipt_unique_id')->label('課金ID'),
                TextColumn::make('os_platform')->label('OS'),
                TextColumn::make('billing_platform')->label('課金PF'),
                TextColumn::make('log_currency_type')->label('一次通貨区分'),
                TextColumn::make('log_change_amount')->label('追加/変更'),
                TextColumn::make('trigger_name')->label('購入/消費した商品')
                    ->getStateUsing(function (Model $row): string {
                        // idとnameを結合して返す
                        $result = "";
                        if (!blank($row->trigger_id)) {
                            $result .= $row->trigger_id . ': ';
                        }
                        if (!blank($row->trigger_name)) {
                            $result .= $row->trigger_name;
                        }

                        return $result;
                    }),
                TextColumn::make('created_at')->label('購入/消費日時'),
                TextColumn::make('is_sandbox')->label('サンドボックス')
                    ->getStateUsing(function (Model $row): string {
                        // paidでなければサンドボックス表示しない
                        if ($row->log_currency_type !== 'paid') {
                            return '-';
                        }
                        return $row->is_sandbox ? '◯' : '×';
                    })->alignCenter(),
            ])
            ->filters([
                // 購入商品名絞り込み
                Filter::make('produt_sub_id')
                    ->label('購入商品ID')
                    ->form(
                        [
                            TextInput::make('product_sub_id')
                                ->label('購入商品ID'),
                        ]
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['product_sub_id'])) {
                            return $query;
                        }

                        // TODO: 商品名から諸品IDを検索する
                        //  実際の管理画面では商品名が入力される。
                        //  商品名管理はプロダクト側で行なっているため、ここでは何もしない

                        // log_storesからproduct_sub_idの購入物を検索する
                        $productSubIds = [$data['product_sub_id']];
                        $uniqueIds = LogStore::query()
                            ->where('product_sub_id', $productSubIds)
                            ->get('receipt_unique_id')
                            ->toArray();

                        // そのunique_idを持つレコードを検索する
                        $query->where('receipt_unique_id', $uniqueIds);

                        return $query;
                    }),
                    // OS
                    SelectFilter::make('os_platform')
                    ->label('OSPF')
                    ->options([
                        CurrencyConstants::OS_PLATFORM_IOS => 'iOS - ' . CurrencyConstants::PLATFORM_APPSTORE,
                        CurrencyConstants::OS_PLATFORM_ANDROID => 'Android - ' . CurrencyConstants::PLATFORM_GOOGLEPLAY,
                    ]),

                // 購入した日時
                Filter::make('created_at')
                    ->label('購入日時')
                    ->form(
                        [
                            DatePicker::make('created_from')
                                ->label('検索日時開始'),
                            DatePicker::make('created_until')
                                ->label('終了'),
                        ]
                    )
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        if (!blank($data['created_from'])) {
                            $query->where('created_at', '>=', $data['created_from']);
                        }
                        if (!blank($data['created_until'])) {
                            $query->where('created_at', '<=', $data['created_until']);
                        }
                        // 条件があれば、購入した場合のみ検索するようにする
                        if (!blank($data['created_from']) || !blank($data['created_until'])) {
                            // 有償一次通貨かつ増額している場合
                            $query->where('log_currency_type', 'paid');
                            $query->where('log_change_amount', '>', 0);
                        }

                        return $query;
                    }),

                // 購入/消費した商品名
                Filter::make('trigger_name')
                    ->label('購入/消費商品名')
                    ->form(
                        [
                            TextInput::make('trigger_name')
                                ->label('購入/消費商品名'),
                        ]
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['trigger_name'])) {
                            return $query;
                        }

                        $query->where('trigger_name', $data['trigger_name']);

                        return $query;
                    }),

                // 購入/消費した日時
                Filter::make('trigger_created_at')
                    ->label('購入/消費日時')
                    ->form(
                        [
                            DatePicker::make('created_from')
                                ->label('検索日時開始'),
                            DatePicker::make('created_until')
                                ->label('終了'),
                        ]
                    )
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        if (!blank($data['created_from'])) {
                            $query->where('created_at', '>=', $data['created_from']);
                        }
                        if (!blank($data['created_until'])) {
                            $query->where('created_at', '<=', $data['created_until']);
                        }

                        return $query;
                    }),

                // VIPポイント (上限〜下限)
                Filter::make('vip_point')
                    ->label('VIPポイント')
                    ->form(
                        [
                            TextInput::make('min_vip_point')
                                ->label('最小VIPポイント'),
                            TextInput::make('max_vip_point')
                                ->label('最大VIPポイント'),
                        ]
                    )
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        if (!blank($data['min_vip_point'])) {
                            $query->where('vip_point', '>=', $data['min_vip_point']);
                        }
                        if (!blank($data['max_vip_point'])) {
                            $query->where('vip_point', '<=', $data['max_vip_point']);
                        }

                        return $query;
                    }),

            ])->actions([
                LogCurrencyHistoryDetailAction::make('detail'),
            ], position: ActionsPosition::BeforeColumns);
    }

    /**
     * 課金IDで検索
     *
     * @param string $orderId
     * @return void
     */
    public function onOrderIdUpdated(string $orderId): void
    {
        $this->clearParameters();

        $this->enableList = true;
        $this->orderId = $orderId;
        $table = $this->getTable();
        $this->table($table);
    }

    /**
     * その他の条件で検索
     *
     * @param string $userId
     * @param string $userName
     * @param string $triggerId
     * @param string $triggerName
     * @param string $startDate
     * @param string $endDate
     * @param string $minVipPoint
     * @param string $maxVipPoint
     * @return void
     */
    public function onSearchUpdated(
        string $userId,
        string $userName,
        string $triggerId,
        string $triggerName,
        string $startDate,
        string $endDate,
        string $minVipPoint,
        string $maxVipPoint,
    ) {
        $this->clearParameters();
        $this->enableList = true;

        $this->userId = $userId;
        $this->userName = $userName;
        $this->triggerId = $triggerId;
        $this->triggerName = $triggerName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->minVipPoint = $minVipPoint;
        $this->maxVipPoint = $maxVipPoint;

        $table = $this->getTable();
        $this->table($table);
    }

    /**
     * 保持している検索条件パラメータを消去
     *
     * @return void
     */
    private function clearParameters(): void
    {
        $this->orderId = '';
        $this->userId = '';
        $this->userName = '';
        $this->triggerId = '';
        $this->triggerName = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->minVipPoint = '';
        $this->maxVipPoint = '';
    }

    /**
     * 検索条件からクエリビルダを作成する
     *
     * @param Builder $query
     * @param bool $isPaid  有償一次通貨のログを検索する場合はtrue
     * @return Builder
     */
    private function buildQuery(Builder $query, bool $isPaid): Builder
    {
        if ($this->orderId !== '') {
            // order_idがある場合はそれを使用する
            if ($isPaid) {
                // 有償一次通貨のログにのみreceipt_unique_idがあるので、その場合のみ条件を付与する
                $query->where('receipt_unique_id', $this->orderId);
            } else {
                // 無償一次通貨のログにはreceipt_unique_idがないので、検索結果無しの条件にする
                $query->whereRaw('1 = 0');
            }
        } elseif (
            $this->userId !== '' ||
            $this->userName !== '' ||
            $this->triggerId !== '' ||
            $this->triggerName !== '' ||
            $this->startDate !== '' ||
            $this->endDate !== '' ||
            $this->minVipPoint !== '' ||
            $this->maxVipPoint !== ''
        ) {
            // それ以外の検索条件がある場合はそれを使用する
            if ($this->userId !== '') {
                $query->where('usr_user_id', $this->userId);
            }
            if ($this->userName !== '') {
                // ユーザー名は対象ユーザーを検索して、そのユーザーIDを使用する
                $userIds = UsrUserProfile::query()
                    ->where('name', $this->userName)
                    ->get('usr_user_id')
                    ->toArray();
                $userIds = array_values(array_column($userIds, 'usr_user_id'));
                if ($userIds !== []) {
                    $query->where('usr_user_id', $userIds);
                }
            }
            if ($this->triggerId !== '') {
                $query->where('trigger_id', $this->triggerId);
            }
            if ($this->triggerName !== '') {
                $query->where('trigger_name', $this->triggerName);
            }
            if ($this->startDate !== '') {
                $query->where('created_at', '>=', $this->startDate);
            }
            if ($this->endDate !== '') {
                $query->where('created_at', '<=', $this->endDate);
            }

            // 有償一次通貨のログの場合は、VIPポイントの検索条件を付与する
            if ($isPaid) {
                if ($this->minVipPoint !== '') {
                    $query->where('vip_point', '>=', $this->minVipPoint);
                }
                if ($this->maxVipPoint !== '') {
                    $query->where('vip_point', '<=', $this->maxVipPoint);
                }
            } else {
                if ($this->minVipPoint !== '' || $this->maxVipPoint !== '') {
                // 無償一次通貨のログの場合はvip_pointがないため、検索結果無しの条件にする
                    $query->whereRaw('1 = 0');
                }
            }
        } else {
            // 検索条件が何もない場合は何も表示しないように、検索結果無しの条件にする
            $query->whereRaw('1 = 0');
        }

        return $query;
    }
}
