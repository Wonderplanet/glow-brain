<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Constants\ErrorCode;
use WonderPlanet\Domain\Currency\Entities\AdmForeignCurrencyDailyRateEntity;
use WonderPlanet\Domain\Currency\Entities\AdmForeignCurrencyRateEntity;
use WonderPlanet\Domain\Currency\Entities\CollectFreeCurrencyAdminTrigger;
use WonderPlanet\Domain\Currency\Entities\CurrencyRevertTrigger;
use WonderPlanet\Domain\Currency\Entities\ForeignCurrencyMonthlyRateEntity;
use WonderPlanet\Domain\Currency\Entities\ScrapeForeignCurrencyDailyRateResultEntity;
use WonderPlanet\Domain\Currency\Entities\ScrapeForeignCurrencyRateResultEntity;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyException;
use WonderPlanet\Domain\Currency\Models\AdmForeignCurrencyDailyRate;
use WonderPlanet\Domain\Currency\Models\AdmForeignCurrencyRate;
use WonderPlanet\Domain\Currency\Models\LogCurrencyFree;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;
use WonderPlanet\Domain\Currency\Models\MstStoreProduct;
use WonderPlanet\Domain\Currency\Models\OprProduct;
use WonderPlanet\Domain\Currency\Models\UsrCurrencyPaid;
use WonderPlanet\Domain\Currency\Repositories\AdmForeignCurrencyDailyRateRepository;
use WonderPlanet\Domain\Currency\Repositories\AdmForeignCurrencyRateRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyRevertHistoryFreeLogRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyRevertHistoryPaidLogRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyRevertHistoryRepository;
use WonderPlanet\Domain\Currency\Repositories\MstStoreProductRepository;
use WonderPlanet\Domain\Currency\Repositories\OprProductRepository;
use WonderPlanet\Domain\Currency\Repositories\UnionLogCurrencyRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Utils\Csv\BulkLogCurrencyRevertSearch;
use WonderPlanet\Domain\Currency\Utils\Excel\CollaboAggregation;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyBalanceAggregation;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyBalanceAggregationByForeignCountry;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyBalanceMultipleSheets;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyPaidDetail;
use WonderPlanet\Domain\Currency\Utils\Scrape\ForeignCurrencyDailyRateScrape;
use WonderPlanet\Domain\Currency\Utils\Scrape\ForeignCurrencyRateScrape;

/**
 * 管理ツール向けの通貨関連のサービス
 */
class CurrencyAdminService
{
    public const CARBON_CREATE_TZ_JST = 'Asia/Tokyo';

    // 月末・月中平均の為替相場のページで取得できる外貨一覧
    private const PARSE_CURRENCY_CODES = [
        'USD', 'EUR', 'CAD', 'GBP', 'CHF', 'DKK', 'NOK', 'SEK', 'AUD', 'NZD',
        'HKD', 'SGD', 'SAR', 'AED', 'CNY', 'THB', 'INR', 'PKR', 'KWD', 'QAR',
        'IDR', 'MXN', 'KRW', 'PHP', 'ZAR', 'CZK', 'RUB', 'HUF', 'PLN', 'TRY',
    ];
    // 現地参考為替相場のページで取得できる外貨一覧
    private const PARSE_LOCAL_REFERENCE_CURRENCY_CODES = ['TWD', 'MYR'];

    public function __construct(
        private readonly UsrCurrencyPaidRepository $usrCurrencyPaidRepository,
        private readonly UsrCurrencyFreeRepository $usrCurrencyFreeRepository,
        private readonly LogCurrencyPaidRepository $logCurrencyPaidRepository,
        private readonly LogCurrencyFreeRepository $logCurrencyFreeRepository,
        private readonly LogCurrencyRevertHistoryRepository $logCurrencyRevertHistoryRepository,
        private readonly LogCurrencyRevertHistoryPaidLogRepository $logCurrencyRevertHistoryPaidLogRepository,
        private readonly LogCurrencyRevertHistoryFreeLogRepository $logCurrencyRevertHistoryFreeLogRepository,
        private readonly CurrencyService $currencyService,
        private readonly AdmForeignCurrencyRateRepository $admForeignCurrencyRateRepository,
        private readonly AdmForeignCurrencyDailyRateRepository $admForeignCurrencyDailyRateRepository,
        private readonly OprProductRepository $oprProductRepository,
        private readonly MstStoreProductRepository $mstStoreProductRepository,
        private readonly UnionLogCurrencyRepository $unionLogCurrencyRepository,
    ) {
    }

    /**
     * 指定されたログの内容を元に、通貨を補填する
     *
     * @param string $userId
     * @param array<string> $logCurrencyPaidIds
     * @param array<string> $logCurrencyFreeIds
     * @param string $comment
     * @param int $revertCount // 返却個数
     * @return array<string> 返却したLogCurrencyRevertHistoryのID
     */
    public function revertCurrencyFromLog(
        string $userId,
        array $logCurrencyPaidIds,
        array $logCurrencyFreeIds,
        string $comment,
        int $revertCount,
    ): array {
        // 対象になるログを取得
        // 有償通貨はseq_no降順で取得し、消費順の逆順で返却する
        $logCurrencyPaids = $this->logCurrencyPaidRepository->findByIdsOrderBySeqNo($logCurrencyPaidIds);
        $logCurrencyFrees = $this->logCurrencyFreeRepository->findByIds($logCurrencyFreeIds);

        // 返却個数の最大数を計算する
        $maxRevertCount = 0;
        foreach ($logCurrencyPaids as $logPaid) {
            $maxRevertCount += $logPaid['change_amount'] * -1;
        }
        foreach ($logCurrencyFrees as $logFree) {
            $maxRevertCount += $logFree['change_ingame_amount'] * -1;
            $maxRevertCount += $logFree['change_bonus_amount'] * -1;
            $maxRevertCount += $logFree['change_reward_amount'] * -1;
        }

        // 渡された返却個数の数値を確認する
        // 0以下もしくは最大数より大きい値が渡された場合、エラーにする
        if ($revertCount <= 0 || $maxRevertCount < $revertCount) {
            $paidIds = implode(',', array_column($logCurrencyPaids, 'id'));
            $freeIds = implode(',', array_column($logCurrencyFrees, 'id'));
            throw new WpCurrencyException(
                "Invalid revert count. userId: {$userId}, " .
                "logCurrencyPaidIds: {$paidIds}" .
                "logCurrencyFreeIds: {$freeIds}" .
                "maxRevertCount: {$maxRevertCount}, " .
                "revertCount: {$revertCount}",
                ErrorCode::FAILED_TO_REVERT_INVALID_REVERT_COUNT_FOR_SUM
            );
        }

        // ログのtrigger_type, trigger_id, trigger_name, request_id, created_atで分類する
        //  一つのログに対して複数の通貨がある場合があるため、ログごとにまとめる
        $logCurrencies = $this->groupRevertLogByTrigger('paid', $logCurrencyPaids, []);
        $logCurrencies = $this->groupRevertLogByTrigger('free', $logCurrencyFrees, $logCurrencies);

        // 通貨の補填を行う
        $trigger = new CurrencyRevertTrigger();
        $revertLogPaidIds = [];
        $revertLogFreeIds = [];
        $targetBillingPlatforms = [];
        $revertHistoryIds = [];
        foreach ($logCurrencies as $logCurrency) {
            // 通貨の補填を行う
            $log = $logCurrency['log'];
            $paids = $logCurrency['paid'];
            $frees = $logCurrency['free'];

            // 合計返却個数
            $sumRevertPaidAmount = 0;
            $sumRevertFreeAmount = 0;

            // 返却は有償->無償の順で行う
            // 有償一次通貨
            foreach ($paids as $paid) {
                if ($revertCount <= 0) {
                    // 返却個数が0になっていたら終了
                    break;
                }
                // 対象ログでの返却個数を決定する
                $revertAmount = 0;
                // ログ内での消費個数を+の状態にして保持
                // change_amountは消費ログの変動値のため、消費されている時数値は負数になる
                $changeAmount = $paid->change_amount * -1;
                if ($revertCount <= $changeAmount) {
                    // 返却個数が消費した分と同じかそれより少ない場合、返却個数全部をrevertする
                    $revertAmount = $revertCount;
                } else {
                    // 返却個数が消費した分より大きい場合は、change_amount分を全てrevertする
                    $revertAmount = $changeAmount;
                }
                $revertCount = $this->calcRevertCount($revertCount, $revertAmount, $userId, true, $paid->id);

                $logId = $this->revertCurrencyPaidLog(
                    $userId,
                    $paid,
                    $trigger,
                    $revertAmount,
                );
                $revertLogPaidIds[] = [
                    'logId' => $logId,
                    'revertLogId' => $paid->id,
                ];
                // 返却個数を決定後、合計返却個数の変数に追加する
                $sumRevertPaidAmount += $revertAmount;

                // サマリーを更新するために、対象のbilling_platformを記録する
                $targetBillingPlatforms[] = $paid->billing_platform;
            }
            // ログ用に合計返却個数を負数にする
            $logChangePaidAmount = $sumRevertPaidAmount * -1;
            // 無償一次通貨
            foreach ($frees as $free) {
                if ($revertCount <= 0) {
                    // 返却個数が0になっていたら終了
                    break;
                }
                // 対象ログでの返却個数を決定する
                $revertAmount = 0;
                // ログ内での消費個数を+の状態にして保持
                $changeAmount = ($free->change_ingame_amount
                    + $free->change_bonus_amount
                    + $free->change_reward_amount) * -1;
                if ($revertCount <= $changeAmount) {
                    // 返却個数が消費した分と同じかそれより少ない場合、返却個数全部をrevertする
                    $revertAmount = $revertCount;
                } else {
                    // 返却個数が消費した分より大きい場合は、change_amount分を全てrevertする
                    $revertAmount = $changeAmount;
                }
                $revertCount = $this->calcRevertCount($revertCount, $revertAmount, $userId, false, $free->id);

                $logId = $this->revertCurrencyFreeLog(
                    $userId,
                    $free,
                    $trigger,
                    $revertAmount,
                );
                $revertLogFreeIds[] = [
                    'logId' => $logId,
                    'revertLogId' => $free->id,
                ];
                $sumRevertFreeAmount += $revertAmount;
            }
            // ログ用に合計返却個数を負数にする
            $logChangeFreeAmount = $sumRevertFreeAmount * -1;

            // 返却ログを記録する
            $revertLogId = $this->logCurrencyRevertHistoryRepository->insertRevertHistoryLog(
                $userId,
                $comment,
                $log['trigger_type'],
                $log['trigger_id'],
                $log['trigger_name'],
                $log['trigger_detail'],
                $log['request_id_type'],
                $log['request_id'],
                $log['created_at'],
                $logChangePaidAmount,
                $logChangeFreeAmount,
                $trigger
            );

            // 関連するログを記録する
            //  idを振る必要があるので、1行づついれていく
            foreach ($revertLogPaidIds as $paid) {
                $logId = $paid['logId'];
                $revertPaidLogId = $paid['revertLogId'];
                $this->logCurrencyRevertHistoryPaidLogRepository->insertRevertHistoryPaidLog(
                    $userId,
                    $revertLogId,
                    $logId,
                    $revertPaidLogId,
                );
            }
            foreach ($revertLogFreeIds as $free) {
                $logId = $free['logId'];
                $revertFreeLogId = $free['revertLogId'];
                $this->logCurrencyRevertHistoryFreeLogRepository->insertRevertHistoryFreeLog(
                    $userId,
                    $revertLogId,
                    $logId,
                    $revertFreeLogId,
                );
            }
            $revertHistoryIds[] = $revertLogId;
        }

        // サマリーを更新する
        $targetBillingPlatforms = array_unique($targetBillingPlatforms);
        foreach ($targetBillingPlatforms as $billingPlatform) {
            $this->currencyService->refreshPaidAndFreeCurrencySummary($userId, $billingPlatform);
        }
        if ($targetBillingPlatforms === [] && $revertLogFreeIds !== []) {
            // 有償通貨返却対象がなく無償通貨のみ返却した場合、無償通貨サマリーを最新にする
            $this->currencyService->refreshFreeCurrencySummary($userId);
        }

        return $revertHistoryIds;
    }

    /**
     * 一次通貨の返却数を決定し、不正な値の場合はエラーを返す
     * @param int $revertCount 合計返却数
     * @param int $revertAmount 対象ログの中で返却する数
     * @param string $userId
     * @param bool $isPaidLogRevert
     * @param string $logId
     * @return int このログで返せない(余った)返却数
     * @throws WpCurrencyException
     */
    private function calcRevertCount(
        int $revertCount,
        int $revertAmount,
        string $userId,
        bool $isPaidLogRevert,
        string $logId,
    ): int {
        $newRevertCount = $revertCount - $revertAmount;
        if ($newRevertCount < 0) {
            $logErrorText = "";
            if ($isPaidLogRevert) {
                $logErrorText = "logCurrencyPaidId: {$logId}";
            } else {
                $logErrorText = "logCurrencyFreeId: {$logId}";
            }
            // 返却数を順番に返していって、最終的にマイナスになる = 返却数が消費数より多いということなのでエラーとする
            throw new WpCurrencyException(
                "Invalid revert count. userId: {$userId}, " .
                $logErrorText .
                "revertCount: {$revertCount}" .
                "revertAmount: {$revertAmount}" .
                "newRevertCount: {$newRevertCount}",
                ErrorCode::FAILED_TO_REVERT_INVALID_REVERT_COUNT_IN_REVERTING
            );
        }
        return $newRevertCount;
    }

    /**
     * 有償一次通貨のログを元に、通貨を補填する
     *
     * @param string $userId
     * @param LogCurrencyPaid $logCurrencyPaid
     * @param Trigger $trigger
     * @param int $revertAmount // 返却個数
     * @return string
     */
    private function revertCurrencyPaidLog(
        string $userId,
        LogCurrencyPaid $logCurrencyPaid,
        Trigger $trigger,
        int $revertAmount,
    ): string {
        if ($revertAmount <= 0 || $logCurrencyPaid->change_amount * -1 < $revertAmount) {
            throw new WpCurrencyException(
                "Invalid revert count. userId: {$userId}, " .
                "logCurrencyPaidId: {$logCurrencyPaid->id}, " .
                "revertAmount: {$revertAmount}",
                ErrorCode::FAILED_TO_REVERT_INVALID_REVERT_COUNT_FOR_PAID
            );
        }

        // 対象の有償一次通貨を取得
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findById($userId, $logCurrencyPaid->currency_paid_id);

        // 取得できなければエラー
        if (is_null($usrCurrencyPaid)) {
            throw new WpCurrencyException(
                "usr currency paid is not found. userId: {$userId}, " .
                    "logCurrencyPaidId: {$logCurrencyPaid->id}, " .
                    "currencyPaidId: {$logCurrencyPaid->currency_paid_id}",
                ErrorCode::NOT_FOUND_PAID_CURRENCY
            );
        }

        // 加算した数がpurchase_amountを超える場合は、返却にならないのでエラー
        if ($usrCurrencyPaid->purchase_amount < $usrCurrencyPaid->left_amount + $revertAmount) {
            throw new WpCurrencyException(
                "revert amount is over purchase_amount. userId: {$userId}, " .
                    "logCurrencyPaidId: {$logCurrencyPaid->id}, " .
                    "purchase_amount: {$usrCurrencyPaid->purchase_amount}, " .
                    "usr currency left_amount: {$usrCurrencyPaid->left_amount}, " .
                    "amount: {$revertAmount}",
                ErrorCode::FAILED_TO_REVERT_CURRENCY_BY_OVER_PURCHASE_AMOUNT
            );
        }

        // 念の為、ログのuser_idとseq_noが一致することを確認する
        if ($usrCurrencyPaid->seq_no !== $logCurrencyPaid->seq_no) {
            throw new WpCurrencyException(
                "seq_no is not match. userId: {$userId}, " .
                    "logCurrencyPaidId: {$logCurrencyPaid->id}, " .
                    "usr currency seq_no: {$usrCurrencyPaid->seq_no}, " .
                    "log seq_no: {$logCurrencyPaid->seq_no}",
                ErrorCode::FAILED_TO_REVERT_CURRENCY_BY_NOT_MATCH_SEQ_NO
            );
        }

        // ログのため、処理前の総所持数を取得する
        $beforeAmount = $this->usrCurrencyPaidRepository->sumPaidAmount($userId, $usrCurrencyPaid->billing_platform);

        // 有償一次通貨を返却する
        $this->usrCurrencyPaidRepository->incrementPaidAmount(
            $userId,
            $usrCurrencyPaid->billing_platform,
            $usrCurrencyPaid->id,
            $revertAmount
        );

        // ログを記録する
        $logId = $this->logCurrencyPaidRepository->insertPaidLog(
            $userId,
            $usrCurrencyPaid->os_platform,
            $usrCurrencyPaid->billing_platform,
            $usrCurrencyPaid->seq_no,
            $usrCurrencyPaid->id,
            $usrCurrencyPaid->receipt_unique_id,
            (bool) $usrCurrencyPaid->is_sandbox,
            LogCurrencyPaid::QUERY_UPDATE,
            $usrCurrencyPaid->purchase_price,
            $usrCurrencyPaid->purchase_amount,
            $usrCurrencyPaid->price_per_amount,
            $usrCurrencyPaid->vip_point,
            $usrCurrencyPaid->currency_code,
            $beforeAmount,
            $revertAmount,
            $beforeAmount + $revertAmount,
            $trigger
        );

        // この操作で記録されたログのIDを返す
        return $logId;
    }

    /**
     * 無償一次通貨のログを元に、通貨を返却する
     *
     * @param string $userId
     * @param LogCurrencyFree $logCurrencyFree
     * @param Trigger $trigger
     * @param int $revertAmount
     * @return string
     */
    private function revertCurrencyFreeLog(
        string $userId,
        LogCurrencyFree $logCurrencyFree,
        Trigger $trigger,
        int $revertAmount,
    ): string {
        // 加算する数を取得
        // change_amountは減らされた値が入っているので-1をかける
        $ingameAmount = $logCurrencyFree->change_ingame_amount * -1;
        $bonusAmount = $logCurrencyFree->change_bonus_amount * -1;
        $rewardAmount = $logCurrencyFree->change_reward_amount * -1;
        $macRevertCount = $ingameAmount + $bonusAmount + $rewardAmount;

        if ($revertAmount <= 0 || $macRevertCount < $revertAmount) {
            throw new WpCurrencyException(
                "Invalid revert count. userId: {$userId}, " .
                "logCurrencyFreeId: {$logCurrencyFree->id}, " .
                "revertAmount: {$revertAmount}",
                ErrorCode::FAILED_TO_REVERT_INVALID_REVERT_COUNT_FOR_FREE
            );
        }

        // 返却個数を決定する
        // 順番は消費の逆、ボーナス->リワード->ゲーム内通貨の順で行う
        if ($bonusAmount > 0) {
            if ($bonusAmount > $revertAmount) {
                // ボーナス通貨の消費があり、ボーナス通貨が返却個数よりも多い場合
                // 全てボーナス通貨で返却し、他のamountは0にして返却数を一致させる
                $bonusAmount = $revertAmount;
                $ingameAmount = 0;
                $rewardAmount = 0;
            } else {
                // ボーナス通貨の消費があり、ボーナス通貨が返却個数以下の場合
                // revertAmountからbonusAmount分を消費する
                $revertAmount = $revertAmount - $bonusAmount;
            }
        }
        if ($rewardAmount > 0) {
            if ($rewardAmount > $revertAmount) {
                // リワード通貨の消費があり、リワード通貨が返却個数よりも多い場合
                // リワード通貨で返却し、他のamountは0にして返却数を一致させる
                $ingameAmount = 0;
                $rewardAmount = $revertAmount;
            } else {
                // リワード通貨の消費があり、リワード通貨が返却個数以下の場合
                // revertAmountからrewardAmount分を消費する
                $revertAmount = $revertAmount - $rewardAmount;
            }
        }
        if ($ingameAmount > 0) {
            if ($ingameAmount > $revertAmount) {
                // ゲーム内通貨の消費があり、ゲーム内通貨が返却個数よりも多い場合
                // ゲーム内通貨で返却する
                $ingameAmount = $revertAmount;
            } else {
                // ゲーム内通貨の消費があり、ゲーム内通貨が返却個数以下の場合
                // revertAmountからrewardAmount分を消費する
                $revertAmount = $revertAmount - $ingameAmount;
            }
        }

        // 対象の無償一次通貨を取得
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId($userId);

        // 取得できなければエラー
        if (is_null($usrCurrencyFree)) {
            throw new WpCurrencyException(
                "usr currency free is not found. userId: {$userId}, " .
                    "logCurrencyFreeId: {$logCurrencyFree->id}",
                ErrorCode::NOT_FOUND_FREE_CURRENCY
            );
        }

        // 返却
        $this->usrCurrencyFreeRepository->incrementFreeCurrency(
            $userId,
            $ingameAmount,
            $bonusAmount,
            $rewardAmount
        );

        // ログを記録する
        $logId = $this->logCurrencyFreeRepository->insertFreeLog(
            $userId,
            $logCurrencyFree->os_platform,
            $usrCurrencyFree->ingame_amount,
            $usrCurrencyFree->bonus_amount,
            $usrCurrencyFree->reward_amount,
            $ingameAmount,
            $bonusAmount,
            $rewardAmount,
            $usrCurrencyFree->ingame_amount + $ingameAmount,
            $usrCurrencyFree->bonus_amount + $bonusAmount,
            $usrCurrencyFree->reward_amount + $rewardAmount,
            $trigger
        );

        return $logId;
    }

    /**
     * ログをキーで分類する
     *
     * @param string $type
     * @param array<LogCurrencyPaid|LogCurrencyFree> $logRecords
     * @param array<string, array<string, mixed>> $logCurrencies
     * @return array<string, array<string, mixed>>
     */
    private function groupRevertLogByTrigger(
        string $type,
        array $logRecords,
        array $logCurrencies
    ): array {
        return array_reduce($logRecords, function ($carry, $item) use ($type) {
            $key = implode(
                ':',
                [
                    $item->trigger_type,
                    $item->trigger_id,
                    $item->trigger_name,
                    $item->request_id,
                    // 分類はTZに左右されないようISO8601拡張形式を使う
                    $item->created_at->toIso8601String(),
                ]
            );
            if (!isset($carry[$key])) {
                $carry[$key] = [
                    'log' => [],
                    'paid' => [],
                    'free' => [],
                ];
            }
            // logキーにtrigger_typeなどの条件を含める
            $carry[$key]['log'] = [
                'trigger_type' => $item->trigger_type,
                'trigger_id' => $item->trigger_id,
                'trigger_name' => $item->trigger_name,
                'trigger_detail' => $item->trigger_detail,
                'request_id_type' => $item->request_id_type,
                'request_id' => $item->request_id,
                // created_atはstringにしたときにTZに左右されないようISO8601拡張形式を使う
                'created_at' => $item->created_at->toIso8601String(),
            ];
            // $typeキーに$itemを入れる
            $carry[$key][$type][] = $item;
            return $carry;
        }, $logCurrencies);
    }

    /**
     * 有償・無償ログの初回レコードから年の選択オプションを生成して取得
     *
     * @return string[]
     */
    public function getYearOptions(): array
    {
        $now = Carbon::now();
        /** @var \WonderPlanet\Domain\Currency\Models\LogCurrencyPaid|null $logCurrencyPaid */
        $logCurrencyPaid = $this->logCurrencyPaidRepository->getFirstRecord();

        /** @var Carbon $paidCreatedAt */
        $paidCreatedAt = is_null($logCurrencyPaid) ? $now : $logCurrencyPaid->created_at;

        // 年の範囲をループして配列を生成
        $years = [];
        for ($year = $paidCreatedAt->year; $year <= $now->year; $year++) {
            $yearStr = (string)$year;
            $years[$yearStr] = $yearStr;
        }

        return $years;
    }

    /**
     * 一次通貨残高集計エクセルバイナリファイルを取得
     *
     * @param string $year
     * @param string $month
     * @param bool $outputBalanceAggregationAll
     * @param bool $outputBalanceAggregationApple
     * @param bool $outputBalanceAggregationGoogle
     * @param bool $outputPaidDetailAll
     * @param bool $outputPaidDetailApple
     * @param bool $outputPaidDetailGoogle
     * @param bool $outputForeignCountry
     * @param bool $isIncludeSandbox
     * @return CurrencyBalanceMultipleSheets
     */
    public function makeExcelCurrencyBalanceAggregation(
        string $year,
        string $month,
        bool $outputBalanceAggregationAll,
        bool $outputBalanceAggregationApple,
        bool $outputBalanceAggregationGoogle,
        bool $outputPaidDetailAll,
        bool $outputPaidDetailApple,
        bool $outputPaidDetailGoogle,
        bool $outputForeignCountry,
        bool $isIncludeSandbox,
    ): CurrencyBalanceMultipleSheets {
        // 日本時間として月末最終日を生成
        $yearMonth = Carbon::createFromFormat('Y-m', "{$year}-{$month}", self::CARBON_CREATE_TZ_JST);
        $endAt = $yearMonth->lastOfMonth()->endOfDay();

        $exports = [];
        if ($outputBalanceAggregationAll) {
            // 日本累計 全プラットフォーム
            $exports[] = $this->getCurrencyBalanceAggregation($endAt, $isIncludeSandbox, null);
        }
        if ($outputBalanceAggregationApple) {
            // 日本累計 AppStoreのみ
            $exports[] = $this->getCurrencyBalanceAggregation(
                $endAt,
                $isIncludeSandbox,
                CurrencyConstants::PLATFORM_APPSTORE
            );
        }
        if ($outputBalanceAggregationGoogle) {
            // 日本累計 GooglePlayのみ
            $exports[] = $this->getCurrencyBalanceAggregation(
                $endAt,
                $isIncludeSandbox,
                CurrencyConstants::PLATFORM_GOOGLEPLAY
            );
        }
        if ($outputPaidDetailAll) {
            // 日本内訳 全プラットフォーム
            $exports[] = $this->getCurrencyPaidBalanceDetail($endAt, $isIncludeSandbox, null);
        }
        if ($outputPaidDetailApple) {
            // 日本内訳 AppStoreのみ
            $exports[] = $this->getCurrencyPaidBalanceDetail(
                $endAt,
                $isIncludeSandbox,
                CurrencyConstants::PLATFORM_APPSTORE
            );
        }
        if ($outputPaidDetailGoogle) {
            // 日本内訳 GooglePlayのみ
            $exports[] = $this->getCurrencyPaidBalanceDetail(
                $endAt,
                $isIncludeSandbox,
                CurrencyConstants::PLATFORM_GOOGLEPLAY
            );
        }
        if ($outputForeignCountry) {
            // 海外
            $exports[] = $this->getCurrencyBalanceAggregationByForeignCountry($endAt, $isIncludeSandbox);
        }

        return new CurrencyBalanceMultipleSheets($year, $month, $exports, $isIncludeSandbox);
    }

    /**
     * 一次通貨残高集計情報を取得
     *
     * @param Carbon $endAt
     * @param bool $isIncludeSandbox
     * @param string|null $billingPlatform
     * @return CurrencyBalanceAggregation
     */
    public function getCurrencyBalanceAggregation(
        Carbon $endAt,
        bool $isIncludeSandbox,
        ?string $billingPlatform
    ): CurrencyBalanceAggregation {
        // 有償一次通貨ログデータ取得
        $logCurrencyPaidCollection = $this->logCurrencyPaidRepository
            ->getCurrencyAggregationByJPY($endAt, $isIncludeSandbox, $billingPlatform);

        if ($logCurrencyPaidCollection->isEmpty()) {
            // 有償データが存在しない場合は空データを返す
            return new CurrencyBalanceAggregation(
                $endAt,
                collect(),
                $billingPlatform
            );
        }

        // 有償一次通貨集計
        $soldAmountMoney = '0.0';
        $consumeAmountMoney = '0.0';
        $remainingAmountMoney = '0.0';
        [
            $soldAmountByPaid,
            $consumeAmountByPaid,
            $invalidPaidAmount,
            $remainingAmountByPaid,
            $tmpSoldAmountMoney,
            $tmpConsumeAmountMoney,
            $tmpRemainingAmountMoney,
        ] = $this->getAggregatedFromArray($logCurrencyPaidCollection->toArray());

        $soldAmountMoney = bcadd($soldAmountMoney, $tmpSoldAmountMoney, 8);
        $consumeAmountMoney = bcadd($consumeAmountMoney, $tmpConsumeAmountMoney, 8);
        $remainingAmountMoney = bcadd($remainingAmountMoney, $tmpRemainingAmountMoney, 8);

        $summaryData = [
            'soldAmountByPaid' => (string) $soldAmountByPaid,
            'consumeAmountByPaid' => (string) $consumeAmountByPaid,
            'invalidPaidAmount' => (string) $invalidPaidAmount,
            // remainingAmountByPaid(残個数)がマイナス値になる場合は0とする
            'remainingAmountByPaid' => $remainingAmountByPaid >= 0 ? (string) $remainingAmountByPaid : '0',
            'soldAmountMoney' => $soldAmountMoney,
            'consumeAmountMoney' => $consumeAmountMoney,
            // remainingAmountMoney(残高)がマイナス値になる場合は0とする
            'remainingAmountMoney' => bccomp($remainingAmountMoney, '0', 8) >= 0
                ? $remainingAmountMoney
                : '0',
        ];

        return new CurrencyBalanceAggregation(
            $endAt,
            collect($summaryData),
            $billingPlatform
        );
    }

    /**
     * 有償一次通貨残高集計の単価ごとの内訳を取得
     *
     * @param Carbon $endAt
     * @param bool $isIncludeSandbox
     * @param string|null $billingPlatform
     * @return CurrencyPaidDetail
     */
    public function getCurrencyPaidBalanceDetail(
        Carbon $endAt,
        bool $isIncludeSandbox,
        ?string $billingPlatform
    ): CurrencyPaidDetail {
        // 有償一次通貨ログデータ取得
        $logCurrencyPaidCollection = $this->logCurrencyPaidRepository
            ->getCurrencyAggregationByJPY($endAt, $isIncludeSandbox, $billingPlatform);

        if ($logCurrencyPaidCollection->isEmpty()) {
            return new CurrencyPaidDetail($endAt, collect(), $billingPlatform);
        }

        $results = [];

        $logCurrencyPaidCollection
            ->groupBy('price_per_amount')
            ->map(function (Collection $logCurrencyPaidCollection, string $pricePerAmount) use (&$results) {
                // 有償通貨残高集計(単価ごとに集計)
                [
                    $soldAmountByPaid,
                    $consumeAmountByPaid,
                    $invalidPaidAmount,
                    $remainingAmountByPaid,
                    $soldAmountMoney,
                    $consumeAmountMoney,
                    $remainingAmountMoney,
                ] = $this->getAggregatedFromArray($logCurrencyPaidCollection->toArray());

                // $resultsのkeyに$pricePerAmountが存在するかチェック
                if (isset($results[$pricePerAmount])) {
                    // 存在する場合は、対象のデータに加算する
                    $resultRow = $results[$pricePerAmount];
                    $newRow['soldAmountByPaid'] = $resultRow['soldAmountByPaid'] + $soldAmountByPaid;
                    $newRow['consumeAmountByPaid'] = $resultRow['consumeAmountByPaid'] + $consumeAmountByPaid;
                    $newRow['invalidPaidAmount'] = $resultRow['invalidPaidAmount'] + $invalidPaidAmount;
                    $newRow['remainingAmountByPaid'] = $resultRow['remainingAmountByPaid'] + $remainingAmountByPaid;
                    $newRow['soldAmountMoney'] = bcadd($resultRow['soldAmountMoney'], $soldAmountMoney, 8);
                    $newRow['consumeAmountMoney'] = bcadd($resultRow['consumeAmountMoney'], $consumeAmountMoney, 8);
                    $newRow['remainingAmountMoney'] = bcadd(
                        $resultRow['remainingAmountMoney'],
                        $remainingAmountMoney,
                        8
                    );
                    $results[$pricePerAmount] = $newRow;
                    return;
                }

                // 追加
                $results[$pricePerAmount] = [
                    'soldAmountByPaid' => $soldAmountByPaid,
                    'consumeAmountByPaid' => $consumeAmountByPaid,
                    'invalidPaidAmount' => $invalidPaidAmount,
                    'remainingAmountByPaid' => $remainingAmountByPaid,
                    'soldAmountMoney' => $soldAmountMoney,
                    'consumeAmountMoney' => $consumeAmountMoney,
                    'remainingAmountMoney' => $remainingAmountMoney,
                ];
            });

        $formatResults = [];
        foreach ($results as $pricePerAmount => $row) {
            $newResult['soldAmountByPaid'] = (string) $row['soldAmountByPaid'];
            $newResult['consumeAmountByPaid'] = (string) $row['consumeAmountByPaid'];
            $newResult['invalidPaidAmount'] = (string) $row['invalidPaidAmount'];
            // remainingAmountByPaid(残個数)がマイナス値になる場合は0とする
            $newResult['remainingAmountByPaid'] = (int) $row['remainingAmountByPaid'] >= 0
                ? (string) $row['remainingAmountByPaid']
                : '0';
            $newResult['pricePerAmount'] = $pricePerAmount;
            $newResult['soldAmountMoney'] = $row['soldAmountMoney'];
            $newResult['consumeAmountMoney'] = $row['consumeAmountMoney'];
            // remainingAmountMoney(残高)がマイナス値になる場合は0とする
            $newResult['remainingAmountMoney'] = bccomp($row['remainingAmountMoney'], '0', 8) >= 0
                ? $row['remainingAmountMoney']
                : '0';
            $formatResults[] = $newResult;
        }

        return new CurrencyPaidDetail($endAt, collect($formatResults), $billingPlatform);
    }

    /**
     * 有償一次通貨残高集計の通貨コードごとの内訳を取得
     *
     * @param Carbon $endAt
     * @param bool $isIncludeSandbox
     * @return CurrencyBalanceAggregationByForeignCountry
     */
    public function getCurrencyBalanceAggregationByForeignCountry(
        Carbon $endAt,
        bool $isIncludeSandbox
    ): CurrencyBalanceAggregationByForeignCountry {
        // 有償一次通貨ログデータ取得
        $logCurrencyPaidCollection = $this->logCurrencyPaidRepository
            ->getCurrencyAggregationByNotJPY($endAt, $isIncludeSandbox);

        if ($logCurrencyPaidCollection->isEmpty()) {
            return new CurrencyBalanceAggregationByForeignCountry(
                $endAt,
                collect()
            );
        }

        $results = [];

        $logCurrencyPaidCollection
            ->groupBy('currency_code')
            ->map(function (Collection $logCurrencyPaidCollection, string $currencyCode) use (&$results) {
                // 有償通貨残高集計(通貨コードごとに集計)
                [
                    $soldAmountByPaid,
                    $consumeAmountByPaid,
                    $invalidPaidAmount,
                    $remainingAmountByPaid,
                    $soldAmountMoney, // 未使用
                    $consumeAmountMoney, // 未使用
                    $remainingAmountMoney,
                ] = $this->getAggregatedFromArray($logCurrencyPaidCollection->toArray());

                // $resultsのkeyに$pricePerAmountが存在するかチェック
                if (isset($results[$currencyCode])) {
                    // 存在する場合は、対象のデータに加算する
                    $resultRow = $results[$currencyCode];
                    $newRow['soldAmountByPaid'] = $resultRow['soldAmountByPaid'] + $soldAmountByPaid;
                    $newRow['consumeAmountByPaid'] = $resultRow['consumeAmountByPaid'] + $consumeAmountByPaid;
                    $newRow['invalidPaidAmount'] = $resultRow['invalidPaidAmount'] + $invalidPaidAmount;
                    $newRow['remainingAmountByPaid'] = $resultRow['remainingAmountByPaid'] + $remainingAmountByPaid;
                    $newRow['remainingAmountMoney'] = bcadd(
                        $resultRow['remainingAmountMoney'],
                        $remainingAmountMoney,
                        8
                    );
                    $results[$currencyCode] = $newRow;
                    return;
                }
                // 追加
                $results[$currencyCode] = [
                    'soldAmountByPaid' => $soldAmountByPaid,
                    'consumeAmountByPaid' => $consumeAmountByPaid,
                    'invalidPaidAmount' => $invalidPaidAmount,
                    'remainingAmountByPaid' => $remainingAmountByPaid,
                    'remainingAmountMoney' => $remainingAmountMoney,
                ];
            });

        $rateCollection = $this->getAdmForeignCurrencyRateCollection($endAt->year, $endAt->month);
        $formatResults = [];
        foreach ($results as $currencyCode => $row) {
            // $currencyCodeから外国為替相場月末TTMを取得する
            $rate = $rateCollection->first(function (AdmForeignCurrencyRate $rateRow) use ($currencyCode) {
                return $rateRow->currency_code === $currencyCode;
            });

            $newResult['soldAmountByPaid'] = (string) $row['soldAmountByPaid'];
            $newResult['consumeAmountByPaid'] = (string) $row['consumeAmountByPaid'];
            $newResult['invalidPaidAmount'] = (string) $row['invalidPaidAmount'];
            // remainingAmountByPaid(残個数)がマイナス値になる場合は0とする
            $newResult['remainingAmountByPaid'] = (int) $row['remainingAmountByPaid'] >= 0
                ? (string) $row['remainingAmountByPaid']
                : '0';
            $newResult['currencyCode'] = $currencyCode;
            $newResult['rate'] = is_null($rate) ? '' : $rate->ttm;
            // remainingAmountMoney(残高)がマイナス値になる場合は0とする
            $newResult['remainingAmountMoney'] = bccomp((string) $row['remainingAmountMoney'], '0', 8) >= 0
                ? (string) $row['remainingAmountMoney']
                : '0';
            $newResult['rateCalculatedRemainingAmountMoney'] = ''; // 為替レートが取得できなかった時用に空文字で初期化
            if (!is_null($rate)) {
                // 取得できているなら月末ttmと現地通貨額で計算
                $ttm = (string) $rate->ttm;
                $remainingAmountMoney = $row['remainingAmountMoney'];
                $tmpCalculatedRemainingAmountMoney = bcmul($ttm, $remainingAmountMoney, 8);
                // 現地通貨額の残高がマイナス値になる場合は0とする
                $newResult['rateCalculatedRemainingAmountMoney'] =
                    bccomp($tmpCalculatedRemainingAmountMoney, '0', 8) >= 0
                        ? $tmpCalculatedRemainingAmountMoney
                        : '0';
            }

            $formatResults[] = $newResult;
        }

        return new CurrencyBalanceAggregationByForeignCountry(
            $endAt,
            collect($formatResults)
        );
    }

    /**
     * 有償通貨の情報を集計して取得
     *
     * @param \WonderPlanet\Domain\Currency\Models\LogCurrencyPaid[] $logCurrencyPaidList
     * @return mixed[]
     */
    private function getAggregatedFromArray(array $logCurrencyPaidList): array
    {
        $soldAmountByPaid = 0; // 有償通貨販売個数
        $consumeAmountByPaid = 0; // 有償通貨消費個数
        $invalidPaidAmount = 0; // 無効有償通貨個数
        $soldAmountMoney = '0'; // 有償通貨販売金額
        $consumeAmountMoney = '0'; // 有償通貨消費金額
        $invalidPaidAmountMoney = '0'; // 無効有償通貨金額
        $checkTriggerTypes = [
            Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN,
            Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_BATCH,
        ];
        foreach ($logCurrencyPaidList as $row) {
            if (in_array($row['trigger_type'], $checkTriggerTypes, true)) {
                // 回収したレコードが存在する場合は、その分販売個数と販売金額も減算させ購入しなかったことにする
                // 販売個数を減算(マイナス値として加算している)
                $soldAmountByPaid += (int) $row['sum_amount'];
                // 販売金額を減算(tmpMoneyはマイナス値として加算している)
                $tmpMoney = bcmul($row['price_per_amount'], $row['sum_amount'], 8);
                $soldAmountMoney = bcadd($soldAmountMoney, $tmpMoney, 8);
                continue;
            }

            if ($row['trigger_type'] === Trigger::TRIGGER_TYPE_DELETE_USER) {
                // 論理削除した分の所持通貨個数を無効有償通貨個数として集計
                $invalidPaidAmount += -1 * ((int) $row['sum_amount']);
                // 無効有償通貨個数分の金額を算出
                $tmpInvalidPaidAmountMoney = bcmul($row['price_per_amount'], (string) $invalidPaidAmount, 8);
                $invalidPaidAmountMoney = bcadd($invalidPaidAmountMoney, $tmpInvalidPaidAmountMoney, 8);

                // 論理削除されたユーザーの販売個数/金額、消費個数/金額はそのまま集計する為
                // 次のループへ進める
                continue;
            }

            if ((int) $row['sum_amount'] < 0) {
                // 累計消費個数と消費金額を加算
                $sumAmount = -1 * ((int) $row['sum_amount']);
                $consumeAmountByPaid += $sumAmount;
                $tmpConsumeMoney = bcmul($row['price_per_amount'], (string) $sumAmount, 8);
                $consumeAmountMoney = bcadd($consumeAmountMoney, $tmpConsumeMoney, 8);
                continue;
            }

            // 累計販売個数と販売金額を加算
            $soldAmountByPaid += (int) $row['sum_amount'];
            $tmpMoney = bcmul($row['price_per_amount'], $row['sum_amount'], 8);
            $soldAmountMoney = bcadd($soldAmountMoney, $tmpMoney, 8);
        }

        // 有償通貨残個数 = 販売個数 - 消費個数 - 無効個数
        $remainingAmountByPaid = $soldAmountByPaid - $consumeAmountByPaid - $invalidPaidAmount;

        // 有償通貨残高 = 販売金額 - 消費金額 - 無効有償通貨金額
        $tmpRemainingAmountMoney = bcsub($soldAmountMoney, $consumeAmountMoney, 8);
        $remainingAmountMoney = bcsub($tmpRemainingAmountMoney, $invalidPaidAmountMoney, 8);

        return [
            $soldAmountByPaid,
            $consumeAmountByPaid,
            $invalidPaidAmount,
            $remainingAmountByPaid,
            $soldAmountMoney,
            $consumeAmountMoney,
            $remainingAmountMoney,
        ];
    }

    /**
     * コラボ消費通貨の集計情報Excelオブジェクトを取得
     *
     * @param Carbon $startAt
     * @param Carbon $endAt
     * @param array<array{type: string, ids: array<string>}> $searchTriggers
     * @param bool $isIncludeSandbox
     * @return CollaboAggregation
     */
    public function makeExcelCollaboAggregation(
        Carbon $startAt,
        Carbon $endAt,
        array $searchTriggers,
        bool $isIncludeSandbox
    ): CollaboAggregation {
        // 集計開始日時以降に一次通貨返却で返却したlog_currency_paids.idの配列を取得
        $revertLogCurrencyPaidIds = $this->logCurrencyRevertHistoryPaidLogRepository
            ->getRevertLogCurrencyPaidIdsByStartAt($startAt);

        // 表示用データの取得
        $collaboAggregation = $this->logCurrencyPaidRepository->getCollaboAggregation(
            $startAt,
            $endAt,
            $searchTriggers,
            $isIncludeSandbox,
            $revertLogCurrencyPaidIds
        );

        // 年月ごとの外貨為替レートデータを取得
        $collaboAggregationCollection = collect($collaboAggregation);
        $yearMonths = $collaboAggregationCollection->pluck('year_month_created_at')->unique();
        $admForeignCurrencyRateCollections = [];
        foreach ($yearMonths as $yearMonth) {
            // admForeignCurrencyRateのCollectionの配列生成
            [$year, $month] = explode('-', $yearMonth);
            $admForeignCurrencyRateCollections[$yearMonth] = $this
                ->getAdmForeignCurrencyRateCollection((int) $year, (int) $month);
        }

        // 集計データをもとに為替レート抽出して新しい配列に格納
        $data = [];
        $collaboAggregationCollection->map(function ($row) use ($admForeignCurrencyRateCollections, &$data) {
            $ttm = '1'; // JPY用にttmを1で初期化
            if ($row['currency_code'] !== 'JPY') {
                // JPY以外なら外貨為替レートからttmを取得
                foreach ($admForeignCurrencyRateCollections as $yearMonth => $coll) {
                    if ($yearMonth !== $row['year_month_created_at']) {
                        // 年月が異なるならスキップ
                        continue;
                    }

                    $admForeignCurrencyRate = $coll->first(function ($adm) use ($row) {
                        return $adm->currency_code === $row['currency_code'];
                    });

                    $ttm = is_null($admForeignCurrencyRate)
                        ? '' // 一致する外貨レートがない(外貨レートに存在しない通貨コードだったなど)場合は空文字にする
                        : $admForeignCurrencyRate->ttm;
                }
            }

            // 月末ttmと有償消費通貨額を追加して新しい配列に追加
            $row['ttm'] = $ttm;
            $row['rate_calculated_money'] = '';
            if ($ttm !== '') {
                // ttmに値があれば有償消費通貨額を計算して追加
                $tmp = bcmul($row['price_per_amount'], $ttm, 8);
                $row['rate_calculated_money'] = bcmul($tmp, $row['sum_amount'], 8);
            }

            $data[] = $row;
        });

        return new CollaboAggregation(
            collect($data),
            $startAt,
            $endAt,
            $searchTriggers,
            $isIncludeSandbox
        );
    }

    /**
     * 対象年月の外貨為替相場データを取得
     *
     * @param int $year
     * @param int $month
     * @return Collection<int, AdmForeignCurrencyRate>
     */
    public function getAdmForeignCurrencyRateCollection(int $year, int $month): Collection
    {
        return $this->admForeignCurrencyRateRepository
            ->getCollectionByYearAndMonth($year, $month);
    }

    /**
     * 対象年月日の外貨為替相場データを取得
     *
     * @param int $year
     * @param int $month
     * @return ForeignCurrencyMonthlyRateEntity
     */
    public function getForeignCurrencyMonthlyRate(int $year, int $month): ForeignCurrencyMonthlyRateEntity
    {
        /** @var Collection<int, AdmForeignCurrencyRateEntity> $admForeignCurrencyRates */
        $admForeignCurrencyRates = $this->admForeignCurrencyRateRepository
            ->getCollectionByYearAndMonth($year, $month)
            ->map(function (AdmForeignCurrencyRate $rate): AdmForeignCurrencyRateEntity {
                return $rate->getModelEntity();
            });

        /** @var Collection<int, AdmForeignCurrencyDailyRateEntity> $admForeignCurrencyDailyRates */
        $admForeignCurrencyDailyRates = $this->admForeignCurrencyDailyRateRepository
            ->getCollectionByYearAndMonth($year, $month)
            ->map(function (AdmForeignCurrencyDailyRate $rate): AdmForeignCurrencyDailyRateEntity {
                return $rate->getModelEntity();
            });

        return new ForeignCurrencyMonthlyRateEntity(
            $year,
            $month,
            $admForeignCurrencyRates,
            $admForeignCurrencyDailyRates
        );
    }

    /**
     * 対象年月の外貨為替相場データを削除
     *
     * @param int $year
     * @param int $month
     */
    public function deleteForeignCurrencyRateByYearAndMonth(int $year, int $month): void
    {
        $this->admForeignCurrencyRateRepository->deleteByYearAndMonth($year, $month);
    }

    /**
     * 対象年月の外貨為替相場データが取得済みか
     *
     * @param int $year
     * @param int $month
     * @return array<string,bool>
     */
    public function existsScrapeForeignCurrencyRateByYearAndMonth(int $year, int $month): array
    {
        // 既にデータがあるかを調べる
        $admForeignCurrencyRateData = $this->getAdmForeignCurrencyRateCollection($year, $month);
        $existParseForeignRate = true;
        $existParseLocalReference = true;
        if ($admForeignCurrencyRateData->isNotEmpty()) {
            // 対象年月の外貨情報が1件でもある場合
            // 現地参考為替相場から取得できる外貨情報があるか調べる
            $twdAndMyrData = $admForeignCurrencyRateData
                ->whereIn('currency_code', self::PARSE_LOCAL_REFERENCE_CURRENCY_CODES);
            if (
                $twdAndMyrData->isEmpty() ||
                $twdAndMyrData->count() !== count(self::PARSE_LOCAL_REFERENCE_CURRENCY_CODES)
            ) {
                $existParseLocalReference = false;
            }
            // 月末・月中平均の為替相場から取得できる外貨情報があるか調べる
            $parseData = $admForeignCurrencyRateData->whereIn('currency_code', self::PARSE_CURRENCY_CODES);
            if ($parseData->isEmpty() || $parseData->count() !== count(self::PARSE_CURRENCY_CODES)) {
                $existParseForeignRate = false;
            }
        } else {
            // 対象年月の外貨情報がない
            $existParseForeignRate = false;
            $existParseLocalReference = false;
        }
        return [
            'existForeignRate' => $existParseForeignRate,
            'existLocalReference' => $existParseLocalReference,
        ];
    }

    /**
     * 外貨為替相場データの登録実行
     *
     * @param int $year
     * @param int $month
     * @return ScrapeForeignCurrencyRateResultEntity
     */
    public function scrapeForeignCurrencyRate(int $year, int $month): ScrapeForeignCurrencyRateResultEntity
    {
        // 既にデータがあるかを調べる
        [
            'existForeignRate' => $existParseForeignRate,
            'existLocalReference' => $existParseLocalReference,
        ] = $this->existsScrapeForeignCurrencyRateByYearAndMonth($year, $month);

        // 新規取得する必要がない場合はログを記載してreturn
        if ($existParseForeignRate && $existParseLocalReference) {
            Log::info("外貨為替定期収集コマンド {$year}年{$month}月末更新分:取得済みの為終了");
            return new ScrapeForeignCurrencyRateResultEntity(true, true);
        }

        // コードから登録に必要なデータを取得
        $foreignCurrencyRateScrape = $this->createForeignCurrencyRateScrape();
        $foreignCurrencyRateData = collect();
        $localReferenceRateData = collect();
        $result = new ScrapeForeignCurrencyRateResultEntity(true, true);

        $isNeedScrapeForeignRate = !$existParseForeignRate && config('wp_currency.enable_scrape_foreign_rate');
        $isNeedScrapeLocalReference = !$existParseLocalReference && config('wp_currency.enable_scrape_local_reference');

        try {
            $foreignCurrencyRateData = $isNeedScrapeForeignRate ?
                $foreignCurrencyRateScrape->parse($year, $month) :
                collect();
        } catch (\Exception $e) {
            // 月末・月中平均の為替相場取得時にエラーが発生した場合
            $message = "外貨為替定期収集コマンド {$year}年{$month}月末更新分:月末・月中平均の為替相場取得時にエラーが発生しました";
            Log::error($message);
            $result->setForeignRateSuccess(false);
            $result->setForeignRateErrorMessage($message);
            $result->setForeignRateException($e);

            // 取得レコードは空にする
            $foreignCurrencyRateData = collect();
        }

        try {
            $localReferenceRateData = $isNeedScrapeLocalReference ?
                $foreignCurrencyRateScrape->parseLocalReferenceExchangeRateByExcel($year, $month) :
                collect();
        } catch (\Exception $e) {
            // 現地参考為替相場取得時にエラーが発生した場合
            $message = "外貨為替定期収集コマンド {$year}年{$month}月末更新分:現地参考為替相場取得時にエラーが発生しました";
            Log::error($message);
            $result->setLocalReferenceSuccess(false);
            $result->setLocalReferenceErrorMessage($message);
            $result->setLocalReferenceException($e);

            // 取得レコードは空にする
            $localReferenceRateData = collect();
        }

        $insertRateData = $foreignCurrencyRateData->merge($localReferenceRateData);

        // 取得は成功していて失敗判定されていないが内容が空の場合、取得が必要な状態であればfalseとする
        if ($isNeedScrapeForeignRate && $result->isForeignRateSuccess() && $foreignCurrencyRateData->isEmpty()) {
            $result->setForeignRateSuccess(false);
            $result->setForeignRateErrorMessage("{$year}年{$month}月 月末・月中平均の為替相場が取得できませんでした");
        }
        if ($isNeedScrapeLocalReference && $result->isLocalReferenceSuccess() && $localReferenceRateData->isEmpty()) {
            $result->setLocalReferenceSuccess(false);
            $result->setLocalReferenceErrorMessage("{$year}年{$month}月 現地参考為替相場が取得できませんでした");
        }
        if ($insertRateData->isEmpty()) {
            // 取得できたデータがない場合はこれ以上続ける必要がないのでログを記載してreturn
            Log::info('外貨為替情報が空だった');

            return $result;
        }

        // データ登録
        // 取得できたデータを登録する。エラーとなったデータは無視する。
        foreach ($insertRateData->toArray() as $row) {
            $this->admForeignCurrencyRateRepository
                ->insert(
                    $year,
                    $month,
                    $row['currency'],
                    $row['currencyName'],
                    $row['currencyCode'],
                    $row['tts'],
                    $row['ttb']
                );
        }

        return $result;
    }


    /**
     * 本日の外貨為替相場データの登録実行
     *
     * @return ScrapeForeignCurrencyDailyRateResultEntity
     */
    public function scrapeForeignCurrencyDailyRate(): ScrapeForeignCurrencyDailyRateResultEntity
    {
        $now = Carbon::now()->setTimezone(self::CARBON_CREATE_TZ_JST);
        $year = $now->year;
        $month = $now->month;
        $day = $now->day;
        $admForeignCurrencyDailyRates = $this->admForeignCurrencyDailyRateRepository
            ->getCollectionByYearAndMonthAndDay($year, $month, $day);

        // 新規取得する必要がない場合はログを記載してreturn
        if ($admForeignCurrencyDailyRates->isNotEmpty()) {
            Log::info("本日の為替定期収集コマンド {$year}年{$month}月{$day}日更新分:取得済みの為終了");
            return new ScrapeForeignCurrencyDailyRateResultEntity(true);
        }

        // コードから登録に必要なデータを取得
        $result = new ScrapeForeignCurrencyDailyRateResultEntity(true);
        $foreignCurrencyRateScrape = $this->createForeignCurrencyDailyRateScrape();
        $isNeedScrapeForeignRate = config('wp_currency.enable_scrape_foreign_rate');

        try {
            $foreignCurrencyRateData = $foreignCurrencyRateScrape->parse($year, $month, $day);
        } catch (\Exception $e) {
            $message = "本日の為替定期収集コマンド {$year}年{$month}月{$day}日更新分:本日の為替相場取得時にエラーが発生しました";
            Log::error($message);
            $result->setForeignRateSuccess(false);
            $result->setForeignRateErrorMessage($message);
            $result->setForeignRateException($e);

            // 取得レコードは空にする
            $foreignCurrencyRateData = collect();
        }
        $insertRateData = $foreignCurrencyRateData;

        // 取得は成功していて失敗判定されていないが内容が空の場合、取得が必要な状態であればfalseとする
        if ($isNeedScrapeForeignRate && $result->isForeignRateSuccess() && $foreignCurrencyRateData->isEmpty()) {
            $result->setForeignRateSuccess(false);
            $result->setForeignRateErrorMessage("{$year}年{$month}月{$day}日 本日の為替相場が取得できませんでした");
        }

        if ($insertRateData->isEmpty()) {
            // 取得できたデータがない場合、銀行が休業日で更新されていない可能性が高いので、
            // 直近日のデータをコピーして本日分とする
            Log::info('本日の為替情報が空だった');
            $latestRate = $this->admForeignCurrencyDailyRateRepository->getLatest();
            if ($latestRate === null) {
                // 最新の為替相場データがない場合は、何もせずにreturn
                Log::info('本日の為替定期収集コマンド 最新の為替相場データがないため、何も登録しませんでした');
                return $result;
            }

            $admForeignCurrencyDailyRates = $this->admForeignCurrencyDailyRateRepository
                ->getCollectionByYearAndMonthAndDay($latestRate->year, $latestRate->month, $latestRate->day);
            $admForeignCurrencyDailyRates->each(function (AdmForeignCurrencyDailyRate $rate) use ($insertRateData) {
                $insertRateData->push([
                    'currency' => $rate->currency,
                    'currencyName' => $rate->currency_name,
                    'currencyCode' => $rate->currency_code,
                    'tts' => $rate->tts,
                    'ttb' => $rate->ttb,
                ]);
            });
        }

        // データ登録

        // 今月指定の場合は今日の為替相場として登録する
        foreach ($insertRateData->toArray() as $row) {
            $this->admForeignCurrencyDailyRateRepository
                ->insert(
                    $year,
                    $month,
                    $day,
                    $row['currency'],
                    $row['currencyName'],
                    $row['currencyCode'],
                    $row['tts'],
                    $row['ttb']
                );
        }

        return $result;
    }

    /**
     * ForeignCurrencyRateScrapeクラスを生成
     *
     * @return ForeignCurrencyRateScrape
     */
    public function createForeignCurrencyRateScrape(): ForeignCurrencyRateScrape
    {
        return new ForeignCurrencyRateScrape();
    }

    /**
     * ForeignCurrencyRateScrapeクラスを生成
     *
     * @return ForeignCurrencyDailyRateScrape
     */
    public function createForeignCurrencyDailyRateScrape(): ForeignCurrencyDailyRateScrape
    {
        return new ForeignCurrencyDailyRateScrape();
    }

    /**
     * 無償通貨の回収を行う
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $type
     * @param integer $amount
     * @param string $triggerDetail
     * @return UsrCurrencySummaryEntity
     */
    public function collectFreeCurrency(
        string $userId,
        string $osPlatform,
        string $type,
        int $amount,
        string $triggerDetail,
    ): UsrCurrencySummaryEntity {
        // Triggerの作成
        $trigger = new CollectFreeCurrencyAdminTrigger(
            $triggerDetail,
        );

        // 無償通貨の消費
        return $this->currencyService->useFree(
            $userId,
            $osPlatform,
            $type,
            $amount,
            $trigger,
        );
    }

    /**
     * 有償一次通貨回収処理内から呼び出しによる無償通貨の回収を行う
     * typeはbonusで固定している
     * triggerは有償一次通貨回収のtriggerを受け取っている
     *
     * @param string $userId
     * @param string $osPlatform
     * @param int $amount
     * @param Trigger $trigger
     * @return void
     */
    public function collectFreeCurrencyByCollectPaid(
        string $userId,
        string $osPlatform,
        int $amount,
        Trigger $trigger,
    ): void {
        $this->currencyService->useFree(
            $userId,
            $osPlatform,
            CurrencyConstants::FREE_CURRENCY_TYPE_BONUS,
            $amount,
            $trigger,
        );
    }

    /**
     * @param string $id
     * @return OprProduct|null
     */
    public function getOprProductById(string $id): ?OprProduct
    {
        return $this->oprProductRepository
            ->findById($id);
    }

    /**
     * @param string $id
     * @return MstStoreProduct|null
     */
    public function getMstStoreProductById(string $id): ?MstStoreProduct
    {
        return $this->mstStoreProductRepository
            ->findById($id);
    }

    /**
     * 有償一次通貨の回収(減算)
     *
     * @param string $userId
     * @param string $billingPlatform
     * @param string $collectTargetReceiptUniqueId
     * @param string $receiptUniqueId
     * @param bool $isSandbox
     * @param Trigger $trigger
     * @return UsrCurrencyPaid
     * @throws \Exception
     */
    public function collectCurrencyPaid(
        string $userId,
        string $billingPlatform,
        string $collectTargetReceiptUniqueId,
        string $receiptUniqueId,
        bool $isSandbox,
        Trigger $trigger
    ): UsrCurrencyPaid {
        // 現在の有償一次通貨の合計値を取得する
        $beforeAmount = $this->usrCurrencyPaidRepository->sumPaidAmount($userId, $billingPlatform);

        // 回収処理
        $usrCurrencyPaid = $this->getUseCurrencyPaidByUserIdAndReceiptUniqueIdAndBillingPlatform(
            $userId,
            $collectTargetReceiptUniqueId,
            $billingPlatform
        );
        $this->usrCurrencyPaidRepository->decrementPaidAmount(
            $usrCurrencyPaid->usr_user_id,
            $usrCurrencyPaid->billing_platform,
            $usrCurrencyPaid->id,
            $usrCurrencyPaid->purchase_amount,
        );

        // summaryを更新
        $currentAmount = $this->currencyService
            ->refreshPaidCurrncySummary($userId, $billingPlatform);

        // 有償一次通貨回収ログ作成
        $this->logCurrencyPaidRepository->insertPaidLog(
            $userId,
            $usrCurrencyPaid->os_platform,
            $billingPlatform,
            $usrCurrencyPaid->seq_no,
            $usrCurrencyPaid->id,
            $receiptUniqueId,
            $isSandbox,
            LogCurrencyPaid::QUERY_UPDATE,
            $usrCurrencyPaid->purchase_price,
            -1 * $usrCurrencyPaid->purchase_amount,
            $usrCurrencyPaid->price_per_amount,
            $usrCurrencyPaid->vip_point,
            $usrCurrencyPaid->currency_code,
            $beforeAmount,
            -1 * $usrCurrencyPaid->purchase_amount,
            $currentAmount,
            $trigger
        );

        // 回収後のusrCurrencyPaidを返す
        return $this->getUseCurrencyPaidByUserIdAndReceiptUniqueIdAndBillingPlatform(
            $userId,
            $collectTargetReceiptUniqueId,
            $billingPlatform
        );
    }

    /**
     * ユーザーID、レシートユニークID、課金プラットフォームを元に有償一次通貨レコードを取得する
     *
     * @param string $userId
     * @param string $receiptUniqueId
     * @param string $billingPlatform
     * @return UsrCurrencyPaid
     * @throws WpCurrencyException
     */
    private function getUseCurrencyPaidByUserIdAndReceiptUniqueIdAndBillingPlatform(
        string $userId,
        string $receiptUniqueId,
        string $billingPlatform
    ): UsrCurrencyPaid {
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository
            ->findByUserIdAndReceiptUniqueIdAndBillingPlatform(
                $userId,
                $receiptUniqueId,
                $billingPlatform
            );

        if (is_null($usrCurrencyPaid)) {
            throw new WpCurrencyException(
                "usr_currency_paid not found userId={$userId},"
                . " receiptUniqueId={$receiptUniqueId},"
                . " billingPlatform={$billingPlatform}",
                ErrorCode::USR_CURRENCY_PAID_NOT_FOUND
            );
        }

        return $usrCurrencyPaid;
    }

    /**
     * 無償一次通貨付与を実行
     *  バッチや管理画面から付与したい場合に使用する
     *
     * @param string $userId
     * @param string $osPlatform
     * @param int $amount
     * @param string $type
     * @param Trigger $trigger
     * @return UsrCurrencySummaryEntity
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
     */
    public function addCurrencyFree(
        string $userId,
        string $osPlatform,
        int $amount,
        string $type,
        Trigger $trigger,
    ): UsrCurrencySummaryEntity {
        return $this->currencyService
            ->addFree(
                $userId,
                $osPlatform,
                $amount,
                $type,
                $trigger
            );
    }

    /**
     * 一次通貨返却(一括)の対象データ情報CSVオブジェクトを取得
     * @param CarbonImmutable $startAt
     * @param CarbonImmutable $endAt
     * @param string $triggerType
     * @param string $triggerId
     * @param bool $isIncludeSandbox
     * @return BulkLogCurrencyRevertSearch
     */
    public function makeCsvBulkLogCurrencyRevertSearch(
        CarbonImmutable $startAt,
        CarbonImmutable $endAt,
        string $triggerType,
        string $triggerId,
        bool $isIncludeSandbox
    ): BulkLogCurrencyRevertSearch {
        // 表示用データの取得
        $logCurrencyData = $this->unionLogCurrencyRepository->getUnionQueryWithExcelSelect(
            $startAt,
            $endAt,
            $triggerType,
            $triggerId,
            $isIncludeSandbox
        )->get()->toArray();

        // 出力するデータ用配列
        $data = [];
        foreach ($logCurrencyData as $row) {
            $carbon = CarbonImmutable::parse($row['consumed_at']);
            $consumedAtJst = $carbon->setTimezone('Asia/Tokyo')->toDateTimeString();
            $data[] = [
                'usr_user_id' => $row['usr_user_id'],
                'consumed_at' => $consumedAtJst,
                'trigger_type' => $row['trigger_type'],
                'trigger_id' => $row['trigger_id'],
                'trigger_name' => $row['trigger_name'],
                'request_id' => $row['request_id'],
                'sum_log_change_amount_paid' => $row['sum_log_change_amount_paid'],
                'sum_log_change_amount_free' => $row['sum_log_change_amount_free'],
                'log_currency_paid_ids' => $row['log_currency_paid_ids'],
                'log_currency_free_ids' => $row['log_currency_free_ids'],
            ];
        }

        /** @var Collection<string, mixed> $dataCollection */
        $dataCollection = collect($data);
        return new BulkLogCurrencyRevertSearch(
            $dataCollection,
            $startAt,
            $endAt,
            $isIncludeSandbox
        );
    }

    /**
     * log_currency_paidsとlog_currency_freesをunionし、消費ログを取得する
     *
     * @param string|null $startAtUtc
     * @param string|null $endAtUtc
     * @param string|null $triggerType
     * @param string|null $triggerId
     * @param string|null $userId
     * @param bool $isIncludeSandbox
     *
     * @return Builder<\WonderPlanet\Domain\Currency\Models\LogCurrencyUnionModel>
     */
    public function getConsumeLogCurrencyPaidAndFrees(
        ?string $startAtUtc,
        ?string $endAtUtc,
        ?string $triggerType,
        ?string $triggerId,
        ?string $userId,
        bool $isIncludeSandbox,
    ): Builder {
        return $this->unionLogCurrencyRepository->getConsumeLogWithUnionQuery(
            $startAtUtc,
            $endAtUtc,
            $triggerType,
            $triggerId,
            $userId,
            $isIncludeSandbox,
        );
    }
}
