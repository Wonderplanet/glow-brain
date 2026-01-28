<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Constants\ErrorCode;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyException;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

/**
 * ユーザーの所持する通貨情報を管理するRepository
 *
 * currency_summaryは現在値のキャッシュとして利用しているため、
 * incrementは行わず、値の操作はinsert/updateで対応する。
 *
 * 更新を行う際も、現在のDB値を反映させるよう注意する
 *
 */
class UsrCurrencySummaryRepository
{
    /**
     * ユーザーの通貨管理情報を返す
     *
     * @param string $userId
     * @return UsrCurrencySummary|null
     */
    public function findByUserId(string $userId): ?UsrCurrencySummary
    {
        return UsrCurrencySummary::query()->where('usr_user_id', $userId)->first() ?? null;
    }

    /**
     * ユーザーの通貨情報を登録する
     *
     * @param string $userId
     * @param integer $freeAmount
     * @return void
     */
    public function insertCurrencySummary(string $userId, int $freeAmount): void
    {
        $usrCurrencySummary = new UsrCurrencySummary();

        $usrCurrencySummary->usr_user_id = $userId;
        $usrCurrencySummary->free_amount = $freeAmount;
        $usrCurrencySummary->save();
    }

    /**
     * 有償一次通貨の所持数を更新する
     *
     * $billingPlatformで更新先を分岐している。
     *   AppStoreまたはGooglePlayを指定すること
     *
     * @param string $userId
     * @param string $billingPlatform
     * @param integer $paidAmount
     * @return void
     */
    public function updateCurrencySummaryPaid(string $userId, string $billingPlatform, int $paidAmount): void
    {
        $this->updateCurrencySummaryPaidAndFreeInternal($userId, $billingPlatform, $paidAmount, null);
    }

    /**
     * 有償・無償一次通貨の所持数を更新する
     *
     * $billingPlatformで更新先を分岐している。
     *   AppStoreまたはGooglePlayを指定すること
     *
     * @param string $userId
     * @param string $billingPlatform
     * @param integer $paidAmount
     * @param integer $freeAmount
     * @return void
     */
    public function updateCurrencySummaryPaidAndFree(
        string $userId,
        string $billingPlatform,
        int $paidAmount,
        int $freeAmount
    ): void {
        $this->updateCurrencySummaryPaidAndFreeInternal($userId, $billingPlatform, $paidAmount, $freeAmount);
    }

    /**
     * 有償・無償一次通貨の所持数を更新する内部処理
     *
     * publicメソッドでは指定する引数を固定したいので、内部処理をprivateにまとめる
     *
     * $billingPlatformで更新先を分岐している。
     *   AppStore、GooglePlay、またはWebStoreを指定すること
     *
     *
     * @param string $userId
     * @param string $billingPlatform
     * @param integer $paidAmount
     * @param integer|null $freeAmount
     * @return void
     */
    private function updateCurrencySummaryPaidAndFreeInternal(
        string $userId,
        string $billingPlatform,
        int $paidAmount,
        ?int $freeAmount
    ): void {
        // $billingPlatformで更新先を分ける
        switch ($billingPlatform) {
            case CurrencyConstants::PLATFORM_APPSTORE:
                $this->updateCurrencySummaryInternal($userId, $paidAmount, null, null, $freeAmount);
                break;
            case CurrencyConstants::PLATFORM_GOOGLEPLAY:
                $this->updateCurrencySummaryInternal($userId, null, $paidAmount, null, $freeAmount);
                break;
            case CurrencyConstants::PLATFORM_WEBSTORE:
                // WebStoreで購入した有償通貨はpaid_amount_shareに保存
                $this->updateCurrencySummaryInternal($userId, null, null, $paidAmount, $freeAmount);
                break;
            default:
                // 想定外のプラットフォームが来る場合は何かおかしいので例外を投げる
                throw new WpCurrencyException(
                    "invalid billing platform: {$billingPlatform}",
                    ErrorCode::UNKNOWN_BILLING_PLATFORM
                );
        }
    }

    /**
     * 無償一次通貨の所持数を更新する
     *
     * @param string $userId
     * @param integer $freeAmount
     * @return void
     */
    public function updateCurrencySummaryFree(string $userId, int $freeAmount): void
    {
        $this->updateCurrencySummaryInternal($userId, null, null, null, $freeAmount);
    }

    /**
     * 通貨情報を0に更新する
     *
     * @param string $userId
     * @return void
     */
    public function updateCurrencySummaryToZero(string $userId): void
    {
        $this->updateCurrencySummaryInternal($userId, 0, 0, 0, 0);
    }

    /**
     * サマリーの各値を更新する
     *
     * サマリーは現在値のキャッシュとして利用しているため、
     * incrementではなく、値の操作はinsert/updateで対応する。
     *
     * update処理を共通化したいので更新メソッドは一つにするが、値がnullのカラムは安全のため更新対象に含めない。
     *
     * 外部から使用すると入力ミスが怖いので、
     * 呼び出し元を限るため、privateにしている
     *
     * @param string $userId
     * @param integer|null $paidAmountApple
     * @param integer|null $paidAmountGoogle
     * @param integer|null $paidAmountShare
     * @param integer|null $freeAmount
     * @return void
     */
    private function updateCurrencySummaryInternal(
        string $userId,
        ?int $paidAmountApple,
        ?int $paidAmountGoogle,
        ?int $paidAmountShare,
        ?int $freeAmount,
    ): void {
        $params = [];
        if (!is_null($paidAmountApple)) {
            $params['paid_amount_apple'] = $paidAmountApple;
        }
        if (!is_null($paidAmountGoogle)) {
            $params['paid_amount_google'] = $paidAmountGoogle;
        }
        if (!is_null($paidAmountShare)) {
            $params['paid_amount_share'] = $paidAmountShare;
        }
        if (!is_null($freeAmount)) {
            $params['free_amount'] = $freeAmount;
        }

        // 更新対象がなにもなければ何もしない
        if ($params === []) {
            return;
        }

        UsrCurrencySummary::query()
            ->where('usr_user_id', $userId)
            ->update($params);
    }

    /**
     * ユーザーの通貨情報を論理削除する
     *
     * @param string $userId
     * @return void
     */
    public function softDeleteByUserId(string $userId): void
    {
        UsrCurrencySummary::query()
            ->where('usr_user_id', $userId)
            ->delete();
    }
}
