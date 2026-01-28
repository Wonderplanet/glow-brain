<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use WonderPlanet\Domain\Currency\Models\LogCurrencyFree;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;
use WonderPlanet\Domain\Currency\Models\LogCurrencyUnionModel;

class UnionLogCurrencyRepository
{
    /**
     * log_currency_paidsとlog_currency_freesを結合し、エクセル用にgroupingしたものを返す
     *
     * @param CarbonImmutable $startAt
     * @param CarbonImmutable $endAt
     * @param string $triggerType
     * @param string $triggerId
     * @param bool $isIncludeSandbox
     *
     * @return Builder<LogCurrencyUnionModel>
     */
    public function getUnionQueryWithExcelSelect(
        CarbonImmutable $startAt,
        CarbonImmutable $endAt,
        string $triggerType,
        string $triggerId,
        bool $isIncludeSandbox,
    ): Builder {
        // startAtとendAtをUTCとして扱う
        $startAtUtc = $startAt->utc()->toDateTimeString();
        $endAtUtc = $endAt->utc()->toDateTimeString();

        $query = $this->getUnionQuery($startAtUtc, $endAtUtc, $triggerType, $triggerId, null);

        // 消費のみを対象とするため、変動した一次通貨の合計値がマイナスのもののみ表示する
        $query->where('log_change_amount', '<', 0);

        if (!$isIncludeSandbox) {
            // sandboxデータを含めない場合は、is_sandbox=falseのデータのみ取得する
            $query->where('is_sandbox', 0);
        }

        // 消費したタイミングごとに集計する
        $groupByColumns = [
            'usr_user_id',
            'trigger_type',
            'trigger_id',
            'trigger_name',
            'created_at',
            'request_id_type',
            'request_id',
        ];
        $query->groupBy($groupByColumns);

        // 表示用の情報
        //   idの項目がないとエラーになるため、適当なUUIDを生成している
        $query->select($groupByColumns);
        $query->selectRaw('created_at as consumed_at');
        $query->selectRaw('UUID() as `id`');
        $query->selectRaw('sum(log_change_amount_paid) as sum_log_change_amount_paid');
        $query->selectRaw('sum(log_change_amount_free) as sum_log_change_amount_free');
        $query->selectRaw('GROUP_CONCAT(log_currency_paid_id) as log_currency_paid_ids');
        $query->selectRaw('GROUP_CONCAT(log_currency_free_id) as log_currency_free_ids');

        // 最新順になるようcreated_at降順にする
        $query->orderBy('created_at', 'desc');

        return $query;
    }

    /**
     * log_currency_paidsとlog_currency_freesの消費ログを取得する
     *
     * @param string|null $startAt
     * @param string|null $endAt
     * @param string|null $triggerType
     * @param string|null $triggerId
     * @param string|null $userId
     * @param bool $isIncludeSandbox
     *
     * @return Builder<LogCurrencyUnionModel>
     */
    public function getConsumeLogWithUnionQuery(
        ?string $startAt,
        ?string $endAt,
        ?string $triggerType,
        ?string $triggerId,
        ?string $userId,
        bool $isIncludeSandbox,
    ): Builder {
        $query = $this->getUnionQuery($startAt, $endAt, $triggerType, $triggerId, $userId);
        // 消費のみを対象とするため、変動した一次通貨の合計値がマイナスのもののみ表示する
        $query->where('log_change_amount', '<', 0);

        if (!$isIncludeSandbox) {
            // sandboxデータを含めない場合は、is_sandbox=falseのデータのみ取得する
            $query->where('is_sandbox', 0);
        }
        return $query;
    }

    /**
     * テーブルに表示するためのクエリを取得する
     * freeとpaidを結合する必要があるのでUNIONしたものを返す
     *
     * @param string|null $startAt
     * @param string|null $endAt
     * @param string|null $triggerType
     * @param string|null $triggerId
     * @param string|null $userId
     *
     * @return Builder<LogCurrencyUnionModel>
     */
    private function getUnionQuery(
        ?string $startAt,
        ?string $endAt,
        ?string $triggerType,
        ?string $triggerId,
        ?string $userId,
    ): Builder {
        // 有償一次通貨と無償一次通貨のログを結合して表示する
        $paidQuery = LogCurrencyPaid::query()
            ->select(
                DB::raw('`id` as `log_currency_paid_id`'),
                DB::raw('\'\' as `log_currency_free_id`'),
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
                'request_id_type',
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
        $logfreeAmountColumns = '`change_ingame_amount` + `change_bonus_amount` + `change_reward_amount`';
        $freeQuery = LogCurrencyFree::query()
            ->select(
                DB::raw('\'\' as `log_currency_paid_id`'),
                DB::raw('`id` as `log_currency_free_id`'),
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
                'request_id_type',
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
                DB::raw($logfreeAmountColumns . ' as `log_change_amount`'),
                // 変動した有償一次通貨の合計値
                DB::raw('0 as `log_change_amount_paid`'),
                // 変動した無償一次通貨の合計値
                DB::raw($logfreeAmountColumns . ' as `log_change_amount_free`'),
            );

        $paidQuery = $this->buildQuery($paidQuery, $startAt, $endAt, $triggerType, $triggerId, $userId);
        $freeQuery = $this->buildQuery($freeQuery, $startAt, $endAt, $triggerType, $triggerId, $userId);

        // paidとfreeをunionした結果を検索クエリとして設定する
        $paidQuery->union($freeQuery);
        return LogCurrencyUnionModel::query()
            ->fromSub($paidQuery, 'log');
    }

    /**
     * 現在のパラメータを元にクエリを作成する
     *
     * @template TModel of \WonderPlanet\Domain\Currency\Models\BaseLogModel
     * @param Builder<TModel> $query
     * @param string|null $startAt
     * @param string|null $endAt
     * @param string|null $triggerType
     * @param string|null $triggerId
     * @param string|null $userId
     *
     * @return Builder<TModel>
     */
    private function buildQuery(
        Builder $query,
        ?string $startAt,
        ?string $endAt,
        ?string $triggerType,
        ?string $triggerId,
        ?string $userId,
    ): Builder {
        if ($userId !== '' && $userId !== null) {
            $query->where('usr_user_id', $userId);
        }
        if ($triggerType !== '' && $triggerType !== null) {
            $query->where('trigger_type', $triggerType);
        }
        if ($triggerId !== '' && $triggerId !== null) {
            $query->where('trigger_id', $triggerId);
        }
        if ($startAt !== '' && $startAt !== null) {
            $query->where('created_at', '>=', $startAt);
        }
        if ($endAt !== '' && $endAt !== null) {
            $query->where('created_at', '<=', $endAt);
        }
        return $query;
    }
}
