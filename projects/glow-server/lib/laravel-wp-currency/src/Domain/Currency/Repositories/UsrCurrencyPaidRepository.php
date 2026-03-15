<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use WonderPlanet\Domain\Currency\Models\UsrCurrencyPaid;

/**
 * ユーザーの有償一次通貨レコードを管理するRepository
 */
class UsrCurrencyPaidRepository
{
    /**
     * user_id別の次のシーケンス番号を取得する
     *
     * @param string $userId
     * @return integer
     */
    public function getNextSeqNo(string $userId): int
    {
        $maxSeqNo = UsrCurrencyPaid::query()
            ->where('usr_user_id', $userId)
            ->max('seq_no');

        return (int)$maxSeqNo + 1;
    }

    /**
     * 有償一次通貨レコードを登録する
     *
     * 単価計算などは呼び出し元で行うこと
     *
     * purchasePrice、pricePerAmountはDBではdecimalで扱われる。
     * PHPで小数値はすべて浮動小数点型(float)となってしまうため、
     * 誤差を防ぐためstringで受け取る
     *
     * クライアントから送信されてくるraw_price_stringでは、'$0.01'のような形式になっているが、
     * currency_paidレコードで扱うのは数値部分となるため、purcahsePriceは'0.01'のような形式で受け取る
     * decimalに代入できる形のsutringにすること
     *
     * seq_noは最大値を指定する。seq_noの昇順に消費される。
     * insertをする前にuser_idごとの最大値を取得し、その+1を指定すること
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param integer $leftAmount
     * @param string $purchasePrice 購入時のストアから送られてくる購入価格
     * @param integer $purchaseAmount 購入数
     * @param string $pricePerAmount 単価 (purchasePrice / purchaseAmount)
     * @param integer $vipPoint
     * @param string $currencyCode
     * @param string $receiptUniqueId
     * @param boolean $isSandbox
     * @return UsrCurrencyPaid
     */
    public function insertUsrCurrencyPaid(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $seqNo,
        int $leftAmount,
        string $purchasePrice,
        int $purchaseAmount,
        string $pricePerAmount,
        int $vipPoint,
        string $currencyCode,
        string $receiptUniqueId,
        bool $isSandbox,
    ): UsrCurrencyPaid {
        $usrCurrencyPaid = new UsrCurrencyPaid();

        $usrCurrencyPaid->usr_user_id = $userId;
        $usrCurrencyPaid->os_platform = $osPlatform;
        $usrCurrencyPaid->billing_platform = $billingPlatform;
        $usrCurrencyPaid->seq_no = $seqNo;
        $usrCurrencyPaid->left_amount = $leftAmount;
        $usrCurrencyPaid->purchase_price = $purchasePrice;
        $usrCurrencyPaid->purchase_amount = $purchaseAmount;
        $usrCurrencyPaid->price_per_amount = $pricePerAmount;
        $usrCurrencyPaid->vip_point = $vipPoint;
        $usrCurrencyPaid->currency_code = $currencyCode;
        $usrCurrencyPaid->receipt_unique_id = $receiptUniqueId;
        // スキーマ上はtinyintのため、intにキャストする
        $usrCurrencyPaid->is_sandbox = (int)$isSandbox;

        $usrCurrencyPaid->save();

        return $usrCurrencyPaid;
    }

    /**
     * 有償一次通貨レコードを取得する
     *
     * 念の為、user_idとidの組み合わせで取得する
     *
     * @param string $userId
     * @param string $id
     * @return UsrCurrencyPaid|null
     */
    public function findById(string $userId, string $id): ?UsrCurrencyPaid
    {
        return UsrCurrencyPaid::query()
            ->where('usr_user_id', $userId)
            ->where('id', $id)
            ->first();
    }

    /**
     * ユーザーの有償一次通貨レコードを返す
     *
     * seq_noの昇順に返す
     *
     * @param string $userId
     * @return array<UsrCurrencyPaid>
     */
    public function findByUserId(string $userId): array
    {
        // レコードの登録順に消費させるため、seq_noで昇順に並べる
        return UsrCurrencyPaid::query()
            ->where('usr_user_id', $userId)
            ->orderBy('seq_no', 'asc')
            ->get()
            ->all();
    }

    /**
     * 指定したプラットフォームの有償一次通貨を全て取得する
     *
     * seq_noの昇順に返す
     *
     * @param string $userId
     * @param string $billingPlatform
     * @return array<UsrCurrencyPaid>
     */
    public function findAllByUserIdAndBillingPlatform(string $userId, string $billingPlatform): array
    {
        // レコードの登録順に消費させるため、seq_noで昇順に並べる
        return UsrCurrencyPaid::query()
            ->where('usr_user_id', $userId)
            ->where('billing_platform', $billingPlatform)
            ->orderBy('seq_no', 'asc')
            ->get()
            ->all();
    }

    /**
     * 残高が有効な有償一次通貨を取得する
     *
     * left_amount=0のものを対象にする。
     * プラス値またはマイナス値の場合は、所持数の計算に反映されているため有効とする。
     *
     * @param string $userId
     * @param string $billingPlatform
     * @return array<UsrCurrencyPaid>
     */
    public function findAllAmountNotZeroPaidByUserIdAndBillingPlatform(string $userId, string $billingPlatform): array
    {
        // レコードの登録順に消費させるため、seq_noで昇順に並べる
        return UsrCurrencyPaid::query()
            ->where('usr_user_id', $userId)
            ->where('billing_platform', $billingPlatform)
            ->whereNot('left_amount', 0)
            ->orderBy('seq_no', 'asc')
            ->get()
            ->all();
    }

    /**
     * 有償一次通貨の所持合計値を取得する
     *
     * @param string $userId
     * @param string $billingPlatform
     * @return integer
     */
    public function sumPaidAmount(string $userId, string $billingPlatform): int
    {
        $sum = UsrCurrencyPaid::query()
            ->where('usr_user_id', $userId)
            ->where('billing_platform', $billingPlatform)
            ->sum('left_amount');
        return (int)$sum;
    }

    /**
     * 有償一次通貨レコードから引き落としを行う
     *
     * 主キーはidだが、無関係なユーザーとプラットフォームから引き落とさないよう、
     * idとuser_idとplatformの組み合わせで引き落としを行う
     *
     * @param string $userId
     * @param string $billingPlatform
     * @param string $id
     * @param integer $amount
     * @return void
     */
    public function decrementPaidAmount(string $userId, string $billingPlatform, string $id, int $amount): void
    {
        UsrCurrencyPaid::query()
            ->where('usr_user_id', $userId)
            ->where('billing_platform', $billingPlatform)
            ->where('id', $id)
            ->decrement('left_amount', $amount);
    }

    /**
     * ユーザーID、レシートユニークID、課金プラットフォームを元に有償一次通貨レコードを取得する
     *
     * @param string $userId
     * @param string $receiptUniqueId
     * @param string $billingPlatform
     * @return UsrCurrencyPaid|null
     */
    public function findByUserIdAndReceiptUniqueIdAndBillingPlatform(
        string $userId,
        string $receiptUniqueId,
        string $billingPlatform
    ): ?UsrCurrencyPaid {
        return UsrCurrencyPaid::query()
            ->where('usr_user_id', $userId)
            ->where('receipt_unique_id', $receiptUniqueId)
            ->where('billing_platform', $billingPlatform)
            ->first();
    }

    /**
     * 有償一次通貨レコードへの加算を行う
     *
     * 行なっていることはdecrementPaidAmountと同じだが、混乱しないようにメソッドを分ける
     *
     * @param string $userId
     * @param string $billingPlatform
     * @param string $id
     * @param integer $amount
     * @return void
     */
    public function incrementPaidAmount(string $userId, string $billingPlatform, string $id, int $amount): void
    {
        UsrCurrencyPaid::query()
            ->where('usr_user_id', $userId)
            ->where('billing_platform', $billingPlatform)
            ->where('id', $id)
            ->increment('left_amount', $amount);
    }

    /**
     * 有償一次通貨レコードを論理削除する
     *
     * @param string $userId
     * @return void
     */
    public function softDeleteByUserId(string $userId): void
    {
        UsrCurrencyPaid::query()
            ->where('usr_user_id', $userId)
            ->delete();
    }
}
