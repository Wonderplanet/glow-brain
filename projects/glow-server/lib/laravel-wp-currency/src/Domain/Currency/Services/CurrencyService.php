<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Services;

use Illuminate\Support\Facades\Config;
use WonderPlanet\Domain\Billing\Delegators\BillingInternalDelegator;
use WonderPlanet\Domain\Currency\Constants\ErrorCode;
use WonderPlanet\Domain\Currency\Entities\FreeCurrencyAddEntity;
use WonderPlanet\Domain\Currency\Entities\LogCurrencyFreeInsertEntity;
use WonderPlanet\Domain\Currency\Entities\PlatformInitTrigger;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Entities\UserDeleteTrigger;
use WonderPlanet\Domain\Currency\Entities\UsrCurrencyFreeEntity;
use WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddFreeCurrencyOverByMaxException;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddPaidCurrencyOverByMaxException;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyException;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;
use WonderPlanet\Domain\Currency\Models\UsrCurrencyPaid;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencySummaryRepository;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

/**
 * ユーザーの通貨情報を管理するService
 */
class CurrencyService
{
    /**
     * 通貨の最大所持数のデフォルト値
     * 通貨の最大所持数は、通貨基盤の設定で変更可能
     * 通貨基盤の設定がない場合はこの値を使用する
     *
     * @var int
     */
    private const DEFAULT_MAX_OWNED_CURRENCY_AMOUNT = 999999999;

    /**
     * 通貨の最大所持数を無制限としたい場合に設定されている値
     *
     * @var int
     */
    private const UNLIMITED_MAX_OWNED_CURRENCY_AMOUNT = -1;

    public function __construct(
        private UsrCurrencySummaryRepository $usrCurrencySummaryRepository,
        private UsrCurrencyFreeRepository $usrCurrencyFreeRepository,
        private UsrCurrencyPaidRepository $usrCurrencyPaidRepository,
        private LogCurrencyFreeRepository $logCurrencyFreeRepository,
        private LogCurrencyPaidRepository $logCurrencyPaidRepository,
        private BillingInternalDelegator $billingInternalDelegator,
    ) {
    }

    /**
     * 課金・通貨基盤の初期データ登録
     *
     * すでにデータがある場合は何もせず、既存データを返す
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param integer $freeAmount
     * @return UsrCurrencySummaryEntity
     */
    public function createUser(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $freeAmount,
    ): UsrCurrencySummaryEntity {
        $usrCurrencySummary = $this->registerCurrencySummary($userId, $osPlatform, $freeAmount);

        return $usrCurrencySummary->getModelEntity();
    }

    /**
     * CurrencySummaryを登録する
     *
     * すでにデータがある場合は何もせず、既存データを返す
     *
     * @param string $userId
     * @param string $osPlatform
     * @param integer $freeAmount
     * @return UsrCurrencySummary
     */
    public function registerCurrencySummary(
        string $userId,
        string $osPlatform,
        int $freeAmount,
    ): UsrCurrencySummary {
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId($userId);
        if ($usrCurrencySummary) {
            return $usrCurrencySummary;
        }

        // データがない場合は登録する
        $this->usrCurrencySummaryRepository->insertCurrencySummary($userId, $freeAmount);
        $this->insertOrIncrementFreeCurrency(
            $userId,
            $osPlatform,
            new FreeCurrencyAddEntity($freeAmount, 0, 0, new PlatformInitTrigger())
        );

        // 登録した後は再度データを取得して返す
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId($userId);

        return $usrCurrencySummary;
    }

    /**
     * サマリーの取得を行う
     *
     * @param string $userId
     * @return UsrCurrencySummaryEntity|null
     */
    public function getCurrencySummary(string $userId): ?UsrCurrencySummaryEntity
    {
        $model = $this->getCurrencySummaryModel($userId);
        return $model ? $model->getModelEntity() : null;
    }

    /**
     * サマリーの取得を行う
     *
     * 取得できなかった場合は例外を投げる
     *
     * @param string $userId
     * @return UsrCurrencySummary
     * @throws WpCurrencyException
     */
    private function getCurrencySummaryWithNullCheck(string $userId): UsrCurrencySummary
    {
        $usrCurrencySummary = $this->getCurrencySummaryModel($userId);
        if (is_null($usrCurrencySummary)) {
            throw new WpCurrencyException(
                "currency summary is not found. userId: {$userId}",
                ErrorCode::NOT_FOUND_CURRENCY_SUMMARY
            );
        }
        return $usrCurrencySummary;
    }

    /**
     * CurrencySummaryのEloquentモデルを明示的に取得する
     *
     * @param string $userId
     * @return UsrCurrencySummary|null
     */
    private function getCurrencySummaryModel(string $userId): ?UsrCurrencySummary
    {
        return $this->usrCurrencySummaryRepository->findByUserId($userId);
    }

    /**
     * 有償一次通貨を追加する
     *
     * currency_summaryの更新も行う
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param integer $amount
     * @param string $currencyCode
     * @param string $price
     * @param integer $vipPoint
     * @param string $receiptUniqueId
     * @param boolean $isSandbox
     * @param Trigger $trigger
     * @return UsrCurrencyPaid
     * @throws WpCurrencyAddCurrencyOverByMaxException
     */
    public function addCurrencyPaid(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $amount,
        string $currencyCode,
        string $price,
        int $vipPoint,
        string $receiptUniqueId,
        bool $isSandbox,
        Trigger $trigger,
    ): UsrCurrencyPaid {
        // 有償一次通貨のレコードを登録する時点でマイナス値だった場合
        //  サーバーAPIから使用するメソッドにおいて、マイナスの残高を登録することはないはずなので、エラーにする
        //  パス商品など、有償一次通貨の設定が0で登録される商品もあるため、0は許容する
        //  課金・消費履歴の検索がlog_currency_paidsを対象にしており、0でも登録しないと検索されないため。
        //  usr_currency_paidは整合性をとるために登録する
        if ($amount < 0) {
            throw new WpCurrencyException(
                "invalid amount. userId: {$userId}, billingPlatform: {$billingPlatform}, amount: {$amount}",
                ErrorCode::FAILED_TO_ADD_PAID_CURRENCY_BY_ZERO
            );
        }

        // 一次通貨付与時のバリデーション
        $this->validateAddCurrency($userId, $amount, 0);

        // ログ記録用
        // 追加前の有償一次通貨の合計値を取得する
        $beforeAmount = $this->usrCurrencyPaidRepository->sumPaidAmount($userId, $billingPlatform);

        // 登録する値を計算
        // この有償一次通貨の単価
        // 金額が絡むので固定小数点数を使う
        // amountが0の場合、有償一次通貨は付与されないため、その単価も0になる
        if ($amount === 0) {
            $pricePerAmount = '0';
        } else {
            $pricePerAmount = bcdiv($price, (string) $amount, 8);
        }

        // 有償一次通貨のレコードを登録
        $nextSeqNo = $this->usrCurrencyPaidRepository->getNextSeqNo($userId);
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            $userId,
            $osPlatform,
            $billingPlatform,
            $nextSeqNo,
            $amount,
            $price,
            $amount,
            $pricePerAmount,
            $vipPoint,
            $currencyCode,
            $receiptUniqueId,
            $isSandbox,
        );

        // currency_summaryの更新
        $currentAmount = $this->refreshPaidCurrncySummary($userId, $billingPlatform);

        // 有償一次通貨のログを追加
        $this->logCurrencyPaidRepository->insertPaidLog(
            $userId,
            $osPlatform,
            $billingPlatform,
            $nextSeqNo,
            $usrCurrencyPaid->id,
            $receiptUniqueId,
            $isSandbox,
            LogCurrencyPaid::QUERY_INSERT,
            $price,
            $amount,
            $pricePerAmount,
            $vipPoint,
            $currencyCode,
            $beforeAmount,
            $amount,
            $currentAmount,
            $trigger
        );

        // 登録されたcurrencyPaidオブジェクトを戻す
        return $usrCurrencyPaid;
    }

    /**
     * ユーザーの有償一次通貨を取得する
     *
     * プラットフォームに関係なく、すべての有償一次通貨を取得する
     *
     * @param string $userId
     * @return array<UsrCurrencyPaid>
     */
    public function getCurrencyPaidAll(
        string $userId,
    ): array {
        return $this->usrCurrencyPaidRepository->findByUserId($userId);
    }

    /**
     * ユーザーの有償一次通貨を消費する
     *
     * - 有償一次通貨は古いものから消費される
     *
     * 詳細は次のURLを参照してください。
     * https://wonderplanet.atlassian.net/wiki/spaces/wonderplanet/pages/106135583
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param integer $amount
     * @param Trigger $trigger
     * @return UsrCurrencySummaryEntity
     * @throws WpCurrencyException
     */
    public function usePaid(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $amount,
        Trigger $trigger
    ): UsrCurrencySummaryEntity {
        // 消費対象の有償一次通貨が足りているかチェックする
        //  サマリーの時点で不足していたらエラーにする
        $beforeSummary = $this->getCurrencySummaryWithNullCheck($userId);
        $beforeAmount =
            $beforeSummary->getPlatformPaidAmount($billingPlatform);
        if ($beforeAmount < $amount) {
            throw new WpCurrencyException(
                "paid summary currency is not enough. userId: {$userId}, billingPlatform: {$billingPlatform}, " .
                    "amount: {$amount}, beforeAmount: {$beforeAmount}",
                ErrorCode::NOT_ENOUGH_PAID_CURRENCY
            );
        }

        // 消費対象の有償一次通貨を取得する
        //  ロックしたコネクションが下手に残ってしまうとまずいので、ロックは行わない
        //  万一トランザクションが衝突するなどで残高がマイナスになってしまった場合、マイナスが許容されているのでそのままマイナス値になる

        // 有償一次通貨の消費を行う
        $leftAmount = $this->usePaidInternal($userId, $osPlatform, $billingPlatform, $amount, $trigger);
        // 引き落としきれなかった場合はエラー
        if ($leftAmount > 0) {
            throw new WpCurrencyException(
                "paid currency is not enough. userId: {$userId}, billingPlatform: {$billingPlatform}, " .
                    "amount: {$amount}, beforeAmount: {$beforeAmount}, leftAmount: {$leftAmount}",
                ErrorCode::NOT_ENOUGH_PAID_CURRENCY
            );
        }

        // summaryを更新する
        $this->refreshPaidCurrncySummary($userId, $billingPlatform);

        return $this->getCurrencySummaryWithNullCheck($userId)->getModelEntity();
    }

    /**
     * 有償一次通貨を消費する
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param integer $amount
     * @param Trigger $trigger
     * @return integer 引き落としきれなかった有償一次通貨を返却する
     */
    private function usePaidInternal(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $amount,
        Trigger $trigger
    ): int {
        $currencuPaids = $this->usrCurrencyPaidRepository->findAllByUserIdAndBillingPlatform($userId, $billingPlatform);

        // ログに記録するため、この課金プラットフォームが所持する変動前の有償一次通貨を合計する
        $beforeAmount = 0;
        foreach ($currencuPaids as $paid) {
            $beforeAmount += $paid->left_amount;
        }

        // amountのぶんだけ引き落とし
        $leftAmount = $amount;
        foreach ($currencuPaids as $paid) {
            // 残高0以下のレコードはスキップする
            if ($paid->left_amount <= 0) {
                continue;
            }

            // 残高が足りている場合は引き落とし
            // 残高が不足していたらあるだけ引き落とし、残りは別のレコードから消費する
            $useAmount = min($paid->left_amount, $leftAmount);

            // 消費はdecrementで行う。残高が0になってもここでは削除しない。
            //   もし該当レコードが他のトランザクションなどで引き落とせる残高ではなくなってしまった場合、マイナス値になる
            $this->usrCurrencyPaidRepository->decrementPaidAmount($userId, $billingPlatform, $paid->id, $useAmount);

            // 有償一次通貨消費ログの追加
            $this->logCurrencyPaidRepository->insertPaidLog(
                $userId,
                $osPlatform,
                $billingPlatform,
                $paid->seq_no,
                $paid->id,
                $paid->receipt_unique_id,
                (bool) $paid->is_sandbox,
                LogCurrencyPaid::QUERY_UPDATE,
                $paid->purchase_price,
                $paid->purchase_amount,
                $paid->price_per_amount,
                $paid->vip_point,
                $paid->currency_code,
                $beforeAmount,
                -$useAmount,
                $beforeAmount - $useAmount,
                $trigger
            );

            // 引き落とした分をleftAmountから減算する
            //  $paid->left_amountのほうが大きい場合、このレコードで全て賄えているはずなので、$useAmount=$leftAmountになっている。
            //  そのためleftAmountは0になる。
            //  そうでなければ$leftAmountのほうが大きいので、次のレコードで引き落としを行うことになる
            $leftAmount -= $useAmount;
            // 引き落とし予定数が0以下になったら終了
            if ($leftAmount <= 0) {
                $leftAmount = 0;
                break;
            }

            // 次のレコード引き落としをする場合、beforeはこのループで引き落とし済みの状態から始める
            $beforeAmount -= $useAmount;
        }

        return $leftAmount;
    }

    /**
     * ユーザーの一次通貨消費を行う
     *
     * 消費優先順について
     * - 無償一次通貨がある場合、無償一次通貨から消費される
     * - 無償一次通貨はingame, reward, bonusの順で消費される
     * - 有償一次通貨は古いものから消費される
     *
     * 詳細は次のURLを参照してください。
     * https://wonderplanet.atlassian.net/wiki/spaces/wonderplanet/pages/106135583
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param integer $amount
     * @param Trigger $trigger
     * @return UsrCurrencySummaryEntity
     * @throws WpCurrencyException
     */
    public function useCurrency(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $amount,
        Trigger $trigger
    ): UsrCurrencySummaryEntity {
        // 消費対象の一次通貨が足りているかチェックする
        //  サマリーの時点で不足していたらエラーにする
        $beforeSumamry = $this->getCurrencySummaryWithNullCheck($userId);
        $beforeAmount =
            $beforeSumamry->getPlatformPaidAmount($billingPlatform) +
            $beforeSumamry->free_amount;
        if ($beforeAmount < $amount) {
            throw new WpCurrencyException(
                "currency summary is not enough. userId: {$userId}, billingPlatform: {$billingPlatform}, " .
                    "amount: {$amount}, beforeAmount: {$beforeAmount}",
                ErrorCode::NOT_ENOUGH_CURRENCY
            );
        }

        // 一次通貨の消費を行う
        //  無償一次通貨から消費する
        //   useFreeInternalはtypeを指定するとそのtypeからのみ引き落とすため、合算の消費ができなくなる
        //   そのためここではnullを指定すること
        $afterAmount = $this->useFreeInternal($userId, $osPlatform, null, $amount, $trigger);
        if ($afterAmount > 0) {
            // 有償一次通貨から引き落とす
            $afterAmount = $this->usePaidInternal($userId, $osPlatform, $billingPlatform, $afterAmount, $trigger);

            // もし有償一次通貨からも引き落としきれなかったらエラー
            if ($afterAmount > 0) {
                throw new WpCurrencyException(
                    "currency is not enough. userId: {$userId}, billingPlatform: {$billingPlatform}, " .
                        "amount: {$amount}, beforeAmount: {$beforeAmount}, afterAmount: {$afterAmount}",
                    ErrorCode::NOT_ENOUGH_CURRENCY
                );
            }
        }

        // summaryを更新する
        $this->refreshPaidAndFreeCurrencySummary($userId, $billingPlatform);

        return $this->getCurrencySummaryWithNullCheck($userId)->getModelEntity();
    }

    /**
     * 無償一次通貨を追加する
     *
     * @param string $userId
     * @param string $osPlatform
     * @param integer $amount
     * @param string $type
     * @param Trigger $trigger
     * @return UsrCurrencySummaryEntity
     * @throws WpCurrencyAddCurrencyOverByMaxException
     */
    public function addFree(
        string $userId,
        string $osPlatform,
        int $amount,
        string $type,
        Trigger $trigger
    ): UsrCurrencySummaryEntity {
        return $this->addFrees(
            $userId,
            $osPlatform,
            [FreeCurrencyAddEntity::fromType($type, $amount, $trigger)]
        );
    }

    /**
     * 無償一次通貨を複数追加する
     *
     * @param string $userId
     * @param string $osPlatform
     * @param array<FreeCurrencyAddEntity> $freeCurrencyAddEntities
     * @return UsrCurrencySummaryEntity
     * @throws WpCurrencyAddCurrencyOverByMaxException
     */
    public function addFrees(
        string $userId,
        string $osPlatform,
        array $freeCurrencyAddEntities
    ): UsrCurrencySummaryEntity {
        // 付与する無償一次通貨の合計を計算
        $totalAmount = 0;
        foreach ($freeCurrencyAddEntities as $freeCurrencyAddEntity) {
            $totalAmount += $freeCurrencyAddEntity->getTotalAmount();
        }

        // 一次通貨付与時のバリデーション
        $this->validateAddCurrency($userId, 0, $totalAmount);

        // 無償一次通貨を増やす、ログを記録する
        $this->insertOrIncrementFreeCurrencies(
            $userId,
            $osPlatform,
            $freeCurrencyAddEntities,
        );

        // summaryを更新する
        $this->refreshFreeCurrencySummary($userId);

        // summaryを返す
        return $this->getCurrencySummary($userId);
    }

    /**
     * 無償一次通貨に引数の値を追加する
     *
     * summaryのアップデートは行わないため、呼び出し元で行うこと
     *
     * @param string $userId
     * @param string $osPlatform
     * @param FreeCurrencyAddEntity $freeCurrencyAddEntity 追加する無償一次通貨の情報
     *
     * @return void
     */
    private function insertOrIncrementFreeCurrency(
        string $userId,
        string $osPlatform,
        FreeCurrencyAddEntity $freeCurrencyAddEntity,
    ): void {
        // 中身はinsertOrIncrementFreeCurrenciesに処理を委譲する
        $this->insertOrIncrementFreeCurrencies(
            $userId,
            $osPlatform,
            [$freeCurrencyAddEntity],
        );
    }

    /**
     * 無償一次通貨の複数追加を行う
     *
     * summaryのアップデートは行わないため、呼び出し元で行うこと
     *
     * @param string $userId
     * @param string $osPlatform
     * @param array<FreeCurrencyAddEntity> $freeCurrencyAddEntities
     * @return void
     */
    private function insertOrIncrementFreeCurrencies(
        string $userId,
        string $osPlatform,
        array $freeCurrencyAddEntities,
    ): void {
        // 更新前の値をとってきて上書きするとまずいので、insertとupdateを分ける
        // この処理開始時点のusrCurrencyFreeを基準にするため、もし他のトランザクションで同時に更新されていた場合、
        //   ログに記録されるbeforeやcurrentの値が現在値と異なる可能性がある
        // 値はDB値に加算しているため、欠損は発生しない
        $beforeIngameAmount = 0;
        $beforeBonusAmount = 0;
        $beforeRewardAmount = 0;

        // すでにある場合はそれを初期値にする
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId($userId);
        if (!is_null($usrCurrencyFree)) {
            $beforeIngameAmount = $usrCurrencyFree->ingame_amount;
            $beforeBonusAmount = $usrCurrencyFree->bonus_amount;
            $beforeRewardAmount = $usrCurrencyFree->reward_amount;
        }

        // 登録するための値の集計とログの作成
        $ingameAmont = 0;
        $bonusAmount = 0;
        $rewardAmount = 0;
        $currentIngameAmount = $beforeIngameAmount;
        $currentBonusAmount = $beforeBonusAmount;
        $currentRewardAmount = $beforeRewardAmount;
        $insertLogEntities = [];
        /** @var \WonderPlanet\Domain\Currency\Entities\FreeCurrencyAddEntity $freeCurrencyAddEntity */
        foreach ($freeCurrencyAddEntities as $freeCurrencyAddEntity) {
            $changeIngameAmount = $freeCurrencyAddEntity->getIngameAmount();
            $changeBonusAmount = $freeCurrencyAddEntity->getBonusAmount();
            $changeRewardAmount = $freeCurrencyAddEntity->getRewardAmount();

            // 最終的に加算する値を計算
            $ingameAmont += $changeIngameAmount;
            $bonusAmount += $changeBonusAmount;
            $rewardAmount += $changeRewardAmount;

            // 現在の値の推移を計算
            $currentIngameAmount += $changeIngameAmount;
            $currentBonusAmount += $changeBonusAmount;
            $currentRewardAmount += $changeRewardAmount;

            // ログ用Entityの作成
            $logEntity = new LogCurrencyFreeInsertEntity(
                $beforeIngameAmount,
                $beforeBonusAmount,
                $beforeRewardAmount,
                $changeIngameAmount,
                $changeBonusAmount,
                $changeRewardAmount,
                $currentIngameAmount,
                $currentBonusAmount,
                $currentRewardAmount,
                $freeCurrencyAddEntity->getTrigger(),
            );
            $insertLogEntities[] = $logEntity;

            // currentが次の処理の開始前beforeになる
            $beforeIngameAmount = $currentIngameAmount;
            $beforeBonusAmount = $currentBonusAmount;
            $beforeRewardAmount = $currentRewardAmount;
        }

        if (is_null($usrCurrencyFree)) {
            // 無償一次通貨の登録
            $this->usrCurrencyFreeRepository->insertFreeCurrency($userId, $ingameAmont, $bonusAmount, $rewardAmount);
        } else {
            // すでにある場合は加算する
            $this->usrCurrencyFreeRepository->incrementFreeCurrency($userId, $ingameAmont, $bonusAmount, $rewardAmount);
        }

        // ログの記録
        $this->logCurrencyFreeRepository->bulkInsertFreeLogs(
            $userId,
            $osPlatform,
            $insertLogEntities,
        );
    }

    /**
     * 無償通貨の消費を行う
     *
     * 消費対象を指定して消費する
     *
     * - タイプが指定されている場合、そのタイプの無償一次通貨を消費する。マイナス値となった場合、マイナス値を格納する
     * - タイプが指定されていない場合、無償一次通貨はingame, reward, bonusの順で消費される
     *
     * 詳細は次のURLを参照してください。
     * https://wonderplanet.atlassian.net/wiki/spaces/wonderplanet/pages/106135583
     *
     * $amountを消費しきれなかった場合に、残りの消費数を返す
     * typeを指定している場合、結果がマイナスとなってもそのtypeから引き落とす。
     * 他のtypeからは消費せず、引き落とせないamountは発生しないため戻り値は常に0となる。使用方法には注意すること
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string|null $type
     * @param integer $amount
     * @param Trigger $trigger
     * @return UsrCurrencySummaryEntity
     */
    public function useFree(
        string $userId,
        string $osPlatform,
        ?string $type,
        int $amount,
        Trigger $trigger
    ): UsrCurrencySummaryEntity {
        // 消費の実行
        $this->useFreeInternal(
            $userId,
            $osPlatform,
            $type,
            $amount,
            $trigger,
        );

        // summaryの更新
        $this->refreshFreeCurrencySummary($userId);

        // summaryを返す
        return $this->getCurrencySummary($userId);
    }

    /**
     * 無償一次通貨を消費する
     *
     * - タイプが指定されている場合、そのタイプの無償一次通貨を消費する。マイナス値となった場合、マイナス値を格納する
     * - タイプが指定されていない場合、無償一次通貨はingame, reward, bonusの順で消費される
     *
     * 詳細は次のURLを参照してください。
     * https://wonderplanet.atlassian.net/wiki/spaces/wonderplanet/pages/106135583
     *
     * $amountを消費しきれなかった場合に、残りの消費数を返す
     * typeを指定している場合、結果がマイナスとなってもそのtypeから引き落とす。
     * 他のtypeからは消費せず、引き落とせないamountは発生しないため戻り値は常に0となる。使用方法には注意すること
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string|null $type
     * @param integer $amount
     * @param Trigger $trigger
     * @return integer 引き落とせなかったぶんのamount
     * @throws WpCurrencyException
     */
    private function useFreeInternal(
        string $userId,
        string $osPlatform,
        ?string $type,
        int $amount,
        Trigger $trigger,
    ): int {
        $beforeUsrCurrencuFree = $this->usrCurrencyFreeRepository->findByUserId($userId);
        if (is_null($beforeUsrCurrencuFree)) {
            // 無償一次通貨がない場合はエラーにする
            throw new WpCurrencyException(
                "free currency is not enough. userId: {$userId}, amount: {$amount}",
                ErrorCode::NOT_FOUND_FREE_CURRENCY
            );
        }

        if (is_null($type)) {
            // それぞれの無償一次通貨で消費する数量を決める
            //   引き落としきれなかった残りは呼び出し元で処理するため、ここでは引き落とし可能な最大値を計算する
            // ※誤配布の回収などで、各所持数はマイナスになっている場合がある。引き落とせる正の数を計算する
            //
            // bonusはプロダクトによって有償一次通貨限定の消費でも使用される可能性があるため、消費順を最後にする
            $ingameAmount = min(max($beforeUsrCurrencuFree->ingame_amount, 0), $amount);
            $rewardAmount = min(max($beforeUsrCurrencuFree->reward_amount, 0), $amount - $ingameAmount);
            $bonusAmount = min(max($beforeUsrCurrencuFree->bonus_amount, 0), $amount - $ingameAmount - $rewardAmount);
        } else {
            // 消費するtypeが指定されているので、そこから落とす
            // タイプが指定されている場合は、指定された数量を引き落とす。その結果マイナスとなることも想定される
            [$ingameAmount, $bonusAmount, $rewardAmount] = CommonUtility::getFreeAmountByType($type, $amount);
        }

        // 引き落とし数量が0の場合は何もしない
        if ($ingameAmount === 0 && $rewardAmount === 0 && $bonusAmount === 0) {
            return $amount;
        }

        // 消費する
        $this->usrCurrencyFreeRepository->decrementFreeCurrency($userId, $ingameAmount, $bonusAmount, $rewardAmount);

        // ログを記録する
        //   もし他のトランザクションでも更新されていた場合を考えて、currentを取り直す
        $usrCurrencuFree = $this->usrCurrencyFreeRepository->findByUserId($userId);

        $this->logCurrencyFreeRepository->insertFreeLog(
            $userId,
            $osPlatform,
            $beforeUsrCurrencuFree->ingame_amount,
            $beforeUsrCurrencuFree->bonus_amount,
            $beforeUsrCurrencuFree->reward_amount,
            -$ingameAmount,
            -$bonusAmount,
            -$rewardAmount,
            $usrCurrencuFree->ingame_amount,
            $usrCurrencuFree->bonus_amount,
            $usrCurrencuFree->reward_amount,
            $trigger
        );

        // summaryの更新は呼び出し元のメソッドで行う
        // 引き落としきれなかった分を戻す
        return $amount - $ingameAmount - $bonusAmount - $rewardAmount;
    }

    /**
     * 有償一次通貨のサマリーを最新の状態にする
     *
     * @param string $userId
     * @param string $billingPlatform
     * @return integer 有償一次通貨の合計値
     */
    public function refreshPaidCurrncySummary(string $userId, string $billingPlatform): int
    {
        // 有償一次通貨の合計値を取得する
        $sum = $this->usrCurrencyPaidRepository->sumPaidAmount($userId, $billingPlatform);

        // summaryを更新する
        $this->usrCurrencySummaryRepository->updateCurrencySummaryPaid($userId, $billingPlatform, $sum);

        // 更新した有償一次通貨の現在の合計値を返す
        return $sum;
    }

    /**
     * 無償一次通貨のサマリーを最新の状態にする
     *
     * @param string $userId
     * @return void
     */
    public function refreshFreeCurrencySummary(string $userId): void
    {
        // 無償一次通貨を取得して合計値を計算する
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId($userId);
        $totalFreeAmount = $usrCurrencyFree->getTotalAmount();

        // summaryを更新する
        $this->usrCurrencySummaryRepository->updateCurrencySummaryFree($userId, $totalFreeAmount);
    }

    /**
     * 有償・無償一次通貨のサマリーを最新の状態にする
     *
     * @param string $userId
     * @param string $billingPlatform
     * @return void
     */
    public function refreshPaidAndFreeCurrencySummary(string $userId, string $billingPlatform): void
    {
        // 有償一次通貨の合計値を取得する
        $paidAmount = $this->usrCurrencyPaidRepository->sumPaidAmount($userId, $billingPlatform);

        // 無償一次通貨を取得して合計値を計算する
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId($userId);
        $totalFreeAmount = $usrCurrencyFree->getTotalAmount();

        // summaryを更新する
        $this->usrCurrencySummaryRepository->updateCurrencySummaryPaidAndFree(
            $userId,
            $billingPlatform,
            $paidAmount,
            $totalFreeAmount
        );
    }

    /**
     * ユーザーの有償一次通貨の所持内訳を取得する
     *
     * @param string $userId
     * @param string $billingPlatform
     * @return array<\WonderPlanet\Domain\Currency\Entities\UsrCurrencyPaidEntity>
     */
    public function getCurrencyPaid(string $userId, string $billingPlatform): array
    {
        $data = $this->usrCurrencyPaidRepository->findAllAmountNotZeroPaidByUserIdAndBillingPlatform(
            $userId,
            $billingPlatform
        );

        // receipt_unique_idの配列を元に商品購入履歴情報を取得
        $receiptUniqueIds = collect($data)->pluck('receipt_unique_id')->toArray();
        $usrStoreProductHistoryCollection = $this->billingInternalDelegator
            ->getUsrStoreProductHistoryCollectionByUserIdAndBillingPlatformAndReceiptUniqueIds(
                $userId,
                $billingPlatform,
                $receiptUniqueIds
            );

        // Entityに置き換え
        $result = array_map(function (UsrCurrencyPaid $model) use ($usrStoreProductHistoryCollection) {
            // 有償一次通貨の購入履歴情報のEntityを取得
            $usrStoreProductHistory = $usrStoreProductHistoryCollection
                ->where(function ($row) use ($model) {
                    return $row['receipt_unique_id'] === $model->receipt_unique_id;
                })->first();

            // Entity取得(管理ツールの有償一次通貨付与(デバッグ)から付与されていると履歴情報はnullになるためnullを許容している)
            $usrStoreProductHistoryEntity = is_null($usrStoreProductHistory)
                ? null
                : $usrStoreProductHistory->getModelEntity();

            // 有償一次通貨modelに履歴情報のEntityを追加
            $model->setUsrStoreProductHistoryEntity($usrStoreProductHistoryEntity);

            return $model->getModelEntity();
        }, $data);

        return $result;
    }

    /**
     * ユーザーの無償一次通貨の所持内訳を取得する
     *
     * @param string $userId
     * @return UsrCurrencyFreeEntity|null
     */
    public function getCurrencyFree(string $userId): ?UsrCurrencyFreeEntity
    {
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId($userId);
        return is_null($usrCurrencyFree) ? null : $usrCurrencyFree->getModelEntity();
    }

    /**
     * 指定されたユーザーIDの通貨情報と課金情報を論理削除する
     *
     * @param string $userId
     * @param string $loggingOsPlatform
     * @return void
     */
    public function softDeleteCurrencyAndBillingDataByUserId(string $userId, string $loggingOsPlatform): void
    {
        // 通貨情報の削除
        $this->softDeleteCurrencyDataByUserId($userId, $loggingOsPlatform);

        // 課金情報の削除
        $this->billingInternalDelegator->softDeleteBillingDataByUserId($userId);
    }

    /**
     * 指定されたユーザーIDの課金情報を論理削除する
     *
     * 論理削除はlaravelのSoftDeletesトレイト機能を使用しているため、モデルなどを変更する場合は注意すること
     *
     * $osPlatformはログの記録用に指定する。
     * (このOSプラットフォームのみではなく、すべて削除対象にする)
     *
     * @param string $userId
     * @param string $loggingOsPlatform
     * @return void
     */
    private function softDeleteCurrencyDataByUserId(string $userId, string $loggingOsPlatform): void
    {
        // 無償一次通貨の削除
        $this->softDeleteCurrencyFreeByUserId($userId, $loggingOsPlatform);

        // 有償一次通貨の削除
        $this->softDeleteCurrencyPaidByUserId($userId);

        // 二次通貨と通貨管理情報の削除
        $this->softDeleteCurrencySummaryByUserId($userId);
    }

    /**
     * 指定されたユーザーIDの無償一次通貨の論理削除
     *
     * $loggingOsPlatformはログの記録用に指定する。
     *
     * @param string $userId
     * @param string $loggingOsPlatform
     * @return void
     */
    private function softDeleteCurrencyFreeByUserId(string $userId, string $loggingOsPlatform): void
    {
        // 無償一次通貨情報を取得
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId($userId);

        // なければ終了
        if (is_null($usrCurrencyFree)) {
            return;
        }

        // 無償一次通貨の残高ぶんだけ消費ログを残す
        $this->logCurrencyFreeRepository->insertFreeLog(
            $userId,
            $loggingOsPlatform,
            $usrCurrencyFree->ingame_amount,
            $usrCurrencyFree->bonus_amount,
            $usrCurrencyFree->reward_amount,
            -$usrCurrencyFree->ingame_amount,
            -$usrCurrencyFree->bonus_amount,
            -$usrCurrencyFree->reward_amount,
            0,
            0,
            0,
            new UserDeleteTrigger($userId, '', '')
        );

        // 無償一次通貨情報を0に更新
        $this->usrCurrencyFreeRepository->decrementFreeCurrency(
            $userId,
            $usrCurrencyFree->ingame_amount,
            $usrCurrencyFree->bonus_amount,
            $usrCurrencyFree->reward_amount
        );

        // 無償一次通貨情報を削除
        $this->usrCurrencyFreeRepository->softDeleteByUserId($userId);
    }

    /**
     * 指定されたユーザーIDの有償一次通貨の論理削除
     *
     * @param string $userId
     * @return void
     */
    private function softDeleteCurrencyPaidByUserId(string $userId): void
    {
        // 優勝一次通貨情報を取得
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findByUserId($userId);

        // なければ終了
        if (count($usrCurrencyPaids) === 0) {
            return;
        }

        // 有償一次通貨の残高レコードを課金プラットフォーム別に仕分け
        $usrCurrencyPaidByPlatform = array_reduce($usrCurrencyPaids, function ($carry, $item) {
            if (!isset($carry[$item->billing_platform])) {
                $carry[$item->billing_platform] = [];
            }
            $carry[$item->billing_platform][] = $item;
            return $carry;
        }, []);

        // 課金プラットフォームごとに処理
        foreach ($usrCurrencyPaidByPlatform as $billingPlatform => $usrCurrencyPaids) {
            // ログに記録するため、この課金プラットフォームが所持する変動前の有償一次通貨を合計する
            $beforeAmount = 0;
            foreach ($usrCurrencyPaids as $usrCurrencyPaid) {
                $beforeAmount += $usrCurrencyPaid->left_amount;
            }

            // 有償一次通貨の残高ぶんだけ消費ログを残す
            // 残高が0でない場合は0に更新もする
            foreach ($usrCurrencyPaids as $usrCurrencyPaid) {
                // 残高が0でも論理削除を行った記録としてログは残す
                $this->logCurrencyPaidRepository->insertPaidLog(
                    $userId,
                    $usrCurrencyPaid->os_platform,
                    $usrCurrencyPaid->billing_platform,
                    $usrCurrencyPaid->seq_no,
                    $usrCurrencyPaid->id,
                    $usrCurrencyPaid->receipt_unique_id,
                    (bool) $usrCurrencyPaid->is_sandbox,
                    LogCurrencyPaid::QUERY_DELETE,
                    $usrCurrencyPaid->purchase_price,
                    $usrCurrencyPaid->purchase_amount,
                    $usrCurrencyPaid->price_per_amount,
                    $usrCurrencyPaid->vip_point,
                    $usrCurrencyPaid->currency_code,
                    $beforeAmount,
                    -$usrCurrencyPaid->left_amount,
                    $beforeAmount - $usrCurrencyPaid->left_amount,
                    new UserDeleteTrigger($userId, $usrCurrencyPaid->id, '')
                );

                // 残高が0でない場合は0に更新する
                //  left_amountはマイナスの場合もある
                if ($usrCurrencyPaid->left_amount !== 0) {
                    $this->usrCurrencyPaidRepository->decrementPaidAmount(
                        $userId,
                        $usrCurrencyPaid->billing_platform,
                        $usrCurrencyPaid->id,
                        $usrCurrencyPaid->left_amount
                    );
                }

                // 次のレコードの処理のため、beforeはこのループで引き落とし済みの状態から始める
                $beforeAmount -= $usrCurrencyPaid->left_amount;
            }
        }

        // 有償一次通貨情報を削除
        $this->usrCurrencyPaidRepository->softDeleteByUserId($userId);
    }

    /**
     * 指定されたユーザーIDの通貨管理情報の論理削除
     *   二次通貨はサマリーでのみ管理しているため、同時に消える
     *
     * $loggingOsPlatformはログの記録用に指定する。
     *
     * @param string $userId
     * @return void
     */
    private function softDeleteCurrencySummaryByUserId(string $userId): void
    {
        // サマリーの残高を0にする
        //   これからサマリーを削除するため、0になっているはず
        //   一次通貨のログはそれぞれのところで出力しているはずなので、ここでは記録しない
        $this->usrCurrencySummaryRepository->updateCurrencySummaryToZero($userId);

        // サマリーの削除
        $this->usrCurrencySummaryRepository->softDeleteByUserId($userId);
    }

    /**
     * 一次通貨の所持数の最大値を取得する
     *
     * 設定はwp_currency.store.max_owned_currency_amountで行う
     * -1が設定されている場合、無制限とする
     *
     * @return integer
     */
    public function getMaxOwnedCurrencyAmount(): int
    {
        return Config::get('wp_currency.store.max_owned_currency_amount', self::DEFAULT_MAX_OWNED_CURRENCY_AMOUNT);
    }

    /**
     * 無償一次通貨の上限所持数を取得する
     *
     * 設定はwp_currency.store.max_owned_free_currency_amountで行う
     * -1が設定されている場合、無制限とする
     *
     * @return integer
     */
    public function getMaxOwnedCurrencyFreeAmount(): int
    {
        // 無償一次通貨の上限所持数はwp_currency.store.max_owned_free_currency_amountで設定する
        return Config::get('wp_currency.store.max_owned_free_currency_amount', self::DEFAULT_MAX_OWNED_CURRENCY_AMOUNT);
    }

    /**
     * 有償一次通貨の上限所持数を取得する
     *
     * 設定はwp_currency.store.max_owned_paid_currency_amountで行う
     * -1が設定されている場合、無制限とする
     *
     * @return integer
     */
    public function getMaxOwnedCurrencyPaidAmount(): int
    {
        // 有償一次通貨の上限所持数はwp_currency.store.max_owned_paid_currency_amountで設定する
        return Config::get('wp_currency.store.max_owned_paid_currency_amount', self::DEFAULT_MAX_OWNED_CURRENCY_AMOUNT);
    }

    /**
     * 一次通貨が付与可能かのバリデーション
     *
     * 失敗した場合、対応する例外が投げられる
     *
     * @param string $userId
     * @param integer $addPaidAmount
     * @param integer $addFreeAmount
     * @return void
     * @throws WpCurrencyAddCurrencyOverByMaxException
     * @throws WpCurrencyAddFreeCurrencyOverByMaxException
     * @throws WpCurrencyAddPaidCurrencyOverByMaxException
     */
    public function validateAddCurrency(string $userId, int $addPaidAmount, int $addFreeAmount)
    {
        // 一次通貨所持上限を超えていないか確認する
        $this->validateMaxOwnedCurrency($userId, $addPaidAmount, $addFreeAmount);
    }

    /**
     * 一次通貨の上限所持が無制限に設定されているか
     *
     * @return boolean
     */
    public function isMaxOwnedCurrencyAmountUnlimited(): bool
    {
        // 値は (UNLIMITED_MAX_OWNED_CURRENCY_AMOUNT = -1) で定義されているので、それと照合する
        //
        // 0や未入力、空文字ではなく-1を明示的に指定させているのは、
        // 設定ミスや取得失敗など意図せぬ理由で値がとれなかったときに無制限に切り替わらないようにするため
        return $this->getMaxOwnedCurrencyAmount() === self::UNLIMITED_MAX_OWNED_CURRENCY_AMOUNT;
    }

    /**
     * 通貨の上限チェックを有償と無償で分けて行うかどうかの設定を取得する
     *
     * true: 有償と無償で分けて上限チェックを行う
     * false: 有償と無償を合算して上限チェックを行う
     *
     * @return boolean
     */
    public function isSeparateCurrencyLimitCheck(): bool
    {
        // wp_currency.store.separate_currency_limit_checkの設定値を取得する
        return Config::get('wp_currency.store.separate_currency_limit_check', false);
    }

    /**
     * 付与予定の一次通貨の所持数が最大値を超えていないかチェックする
     *
     * 最大値を超える場合は例外を投げる
     *
     * @param string $userId
     * @param integer $addPaidAmount
     * @param integer $addFreeAmount
     * @return void
     * @throws WpCurrencyAddCurrencyOverByMaxException
     * @throws WpCurrencyAddFreeCurrencyOverByMaxException
     * @throws WpCurrencyAddPaidCurrencyOverByMaxException
     */
    private function validateMaxOwnedCurrency(string $userId, int $addPaidAmount, int $addFreeAmount)
    {
        // 設定値が無制限の場合はチェックしない
        if ($this->isMaxOwnedCurrencyAmountUnlimited()) {
            return;
        }

        // 現在の所持数を取得するためcunnecy_summaryを取得する
        //   アクセスは増えるかもしれないが念の為、最新のサマリーをここで取得する
        //   サマリー情報は通貨情報が変更されるたびに更新しているため、サマリーの値を信用してチェックをかける
        // 今の処理では主に単体での呼び出しを想定しているが、負荷対策などでループ内で呼ばれるようなことになった場合、
        // summaryを外部から受け取るなど、リファクタが必要になるかもしれない
        $currencySummary = $this->getCurrencySummaryModel($userId);

        if ($this->isSeparateCurrencyLimitCheck()) {
            // 無償と有償で分けて上限チェックを行う
            // 無償通貨
            $afterFreeAmount = $currencySummary->getFreeAmount() + $addFreeAmount;
            $maxownedCurrencyFreeAmount = $this->getMaxOwnedCurrencyFreeAmount();
            if ($afterFreeAmount > $maxownedCurrencyFreeAmount) {
                throw new WpCurrencyAddFreeCurrencyOverByMaxException(
                    $userId,
                    $addFreeAmount,
                    $maxownedCurrencyFreeAmount,
                    $currencySummary
                );
            }
            // 有償通貨
            $afterPaidAmount = $currencySummary->getTotalPaidAmount() + $addPaidAmount;
            $maxownedCurrencyPaidAmount = $this->getMaxOwnedCurrencyPaidAmount();
            if ($afterPaidAmount > $maxownedCurrencyPaidAmount) {
                throw new WpCurrencyAddPaidCurrencyOverByMaxException(
                    $userId,
                    $addPaidAmount,
                    $maxownedCurrencyPaidAmount,
                    $currencySummary
                );
            }
        } else {
            // 無償と有償を合算して上限チェックを行う

            // 加算する一次通貨の合計を計算する
            $addAmount = $addPaidAmount + $addFreeAmount;

            // 最大所持数を取得する
            $maxownedCurrencyAmount = $this->getMaxOwnedCurrencyAmount();

            // 現在の所持数と付与予定の一次通貨の合計が最大所持数を超えていないかチェックする
            $totalAmount = $currencySummary->getTotalCurrencyAmount();

            if ($totalAmount + $addAmount > $maxownedCurrencyAmount) {
                throw new WpCurrencyAddCurrencyOverByMaxException(
                    $userId,
                    $addAmount,
                    $maxownedCurrencyAmount,
                    $currencySummary
                );
            }
        }
    }
}
