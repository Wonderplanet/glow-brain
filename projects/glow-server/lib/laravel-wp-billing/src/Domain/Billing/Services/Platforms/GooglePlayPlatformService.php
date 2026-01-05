<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services\Platforms;

use Google\Client as GoogleClient;
use Google\Service\AndroidPublisher;
use Google\Service\AndroidPublisher\ProductPurchase;
use Google\Service\AndroidPublisher\ProductPurchasesAcknowledgeRequest;
use Illuminate\Support\Facades\Config;
use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Entities\StoreReceipt;
use WonderPlanet\Domain\Billing\Entities\StoreReceiptGooglePlay;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

/**
 * Google PlayStoreプラットフォーム向けサービス
 *
 */
class GooglePlayPlatformService extends BaseStorePlatformService
{
    public const RESPONSE_GOOGLE_OK          = 0;
    public const RESPONSE_GOOGLE_CANCELED    = 1;
    public const RESPONSE_GOOGLE_PENDING     = 2;
    // NOTE 公式ドキュメントにpurchaseState=4は記載がなくサポートに連絡を取り得た情報とのこと
    public const RESPONSE_GOOGLE_CONVENIENCE = 4;

    // 購入承認ステータス(acknowledgementState)
    /** @var int 未承認 */
    public const ACKNOWLEDGEMENT_STATE_YET = 0;
    /** @var int 承認済み */
    public const ACKNOWLEDGEMENT_STATE_ACKNOWLEDGED = 1;

    /**
     * レシートの検証を行う。
     *
     * @param string $billingPlatform
     * @param string $productId
     * @param string $receipt
     * @return StoreReceipt 検証したレシート情報を含むStoreReceiptオブジェクト
     */
    public function verifyReceipt(
        string $billingPlatform,
        string $productId,
        string $receipt,
    ): StoreReceipt {
        // レシートをjsonデコードする
        $receiptJson = json_decode($receipt, true);
        if (is_null($receiptJson)) {
            throw new WpBillingException('invalid receipt, json decode failed.', ErrorCode::INVALID_RECEIPT);
        }
        if (!$this->isGooglePlayStoreReceipt($receiptJson)) {
            // GooglePlayのレシートでなければ無効
            throw new WpBillingException(
                'invalid receipt, This receipt is not google play store receipt.',
                ErrorCode::INVALID_RECEIPT
            );
        }

        // GooglePlayストアのレシートチェック
        //  チェック用のJSONを取り出す
        $payload = json_decode($receiptJson['Payload'], true);
        $json = $payload['json'];
        // Unity IAPのレシートでは、androidのsignatureはPayloadに含まれている
        // @see https://docs.unity3d.com/ja/current/Manual/UnityIAPPurchaseReceipts.html
        $signature = $payload['signature'];
        $this->verifyReceiptSignature($json, $signature);

        $verifiedResponse = $this->verifyReceiptToGooglePlayClient($json);

        $storeReceipt = new StoreReceiptGooglePlay(
            $productId,
            $receipt,
            [$verifiedResponse]
        );
        return $storeReceipt;
    }

    /**
     * レシートJSONデータがGooglePlayStoreのレシートであるかを判定する
     *
     * @param array<mixed> $receiptJson クライアントから送信されたレシートJSON
     * @return boolean
     */
    private function isGooglePlayStoreReceipt(array $receiptJson): bool
    {
        // StoreがAppleAppStoreであること
        if ($receiptJson['Store'] !== 'GooglePlay') {
            return false;
        }

        return true;
    }

    /**
     * 購入レシートがアプリのものかを署名で検証する
     *
     * @param string $payloadJsonString Payload内にあるAndroid課金レシートJSON文字列
     * @param string $signature
     * @return void
     */
    private function verifyReceiptSignature(string $payloadJsonString, string $signature)
    {
        // シグネチャの検証
        // base64_decodeの第二引数がfalseの場合は、base64でない文字列を無視してデコードするため、
        // ここではエラーが発生しない。
        // signatureが一致しない場合は後の検証でエラーとなるため、ここではデコードだけ行う
        $decodedSignature = base64_decode($signature);

        // 公開鍵の読み込み
        $pubkeyFile = $this->getPlayStorePubKey();
        $pubkey = openssl_pkey_get_public($pubkeyFile);

        // pubkeyがfalseの場合は公開鍵が正しく読み込めなかったためエラー
        if ($pubkey === false) {
            throw new WpBillingException(
                "invalid signature: openssl_pkey_get_public failed",
                ErrorCode::GOOGLEPLAY_PUBLIC_KEY_LOAD_FAILED
            );
        }

        // 公開鍵の詳細を取得
        $isValid = openssl_verify($payloadJsonString, $decodedSignature, $pubkey, OPENSSL_ALGO_SHA1);
        if (!$isValid) {
            // 署名の検証に失敗
            throw new WpBillingException("invalid signature: openssl_verify failed", ErrorCode::INVALID_RECEIPT);
        }
    }

    /**
     * レシートの検証を行う
     *
     * ※ProductPurchaseのproductIdについて
     *   戻り値として取得するproductIdについて、「存在しない可能性がある」とドキュメントにあるため、プログラム内では使用しない
     * @see https://developers.google.com/android-publisher/api-ref/rest/v3/purchases.products?hl=ja
     *
     * @param string $encodedReceipt レシートJSON文字列
     * @return ProductPurchase
     */
    private function verifyReceiptToGooglePlayClient(string $encodedReceipt): ProductPurchase
    {
        $receipt = json_decode($encodedReceipt, true);

        if (is_null($receipt)) {
            throw new WpBillingException('invalid receipt, json decode failed.', ErrorCode::INVALID_RECEIPT);
        }

        // 本番環境以外では、orderIdは空になる症状への対応
        if (!CommonUtility::isDebuggableEnvironment() && !isset($receipt['orderId'])) {
            $receipt['orderId'] = 'generated_by_API:' . md5(microtime());
        }

        if ($receipt['packageName'] !== $this->getAndroidPackageName()) {
            // パッケージ名がレシートと一致しない
            throw new WpBillingException(
                "invalid receipt. package_name:{$receipt['packageName']}",
                ErrorCode::INVALID_RECEIPT
            );
        }

        $client = new GoogleClient();
        $scopes = [AndroidPublisher::ANDROIDPUBLISHER];
        $client->setScopes($scopes);
        $client->setAuthConfig($this->getPurchaseCredentialJsonArray());
        $service = new AndroidPublisher($client);

        $productPurchase = $service->purchases_products->get(
            $this->getAndroidPackageName(),
            $receipt['productId'],
            $receipt['purchaseToken']
        );

        // @see https://developers.google.com/android-publisher/api-ref/purchases/products#resource
        $purchaseState = $productPurchase->getPurchaseState();
        if ($purchaseState === self::RESPONSE_GOOGLE_OK) {
            return $productPurchase;
        }

        /// エラーステータスの処理
        if ($purchaseState === self::RESPONSE_GOOGLE_CANCELED) {
            throw new WpBillingException(
                "purchase state canceled. state:$purchaseState",
                ErrorCode::GOOGLEPLAY_RECEIPT_STATUS_CANCELED
            );
        } elseif (
            $purchaseState === self::RESPONSE_GOOGLE_PENDING
            || $purchaseState === self::RESPONSE_GOOGLE_CONVENIENCE
        ) {
            // purchaseState:4は2に相当(UnityIAP使用時に4になる)とのことなのでエラーコードは共通とする
            throw new WpBillingException(
                "purchase state pending. state:$purchaseState",
                ErrorCode::GOOGLEPLAY_RECEIPT_STATUS_PENDING
            );
        } else {
            throw new WpBillingException(
                "can not authorized google store. state:$purchaseState",
                ErrorCode::GOOGLEPLAY_RECEIPT_STATUS_OTHER
            );
        }
    }

    /**
     * パッケージ名を取得
     *
     * @return string
     */
    private function getAndroidPackageName(): string
    {
        return Config::get('wp_currency.store.googleplay_store.package_name');
    }

    /**
     * GOOGLE_APPLICATION_CREDENTIALSのJSON配列を返す
     * PlayStoreからダウンロードしてくる、サービスアカウントのJSONファイルの配列になる
     *
     * setAuthConfigに渡すときに連想配列であればそのまま使用されるため、
     * ここでデコードしておく。
     *
     * purchase_credential_env_keyに指定されている環境変数がデコードできるJSON文字列であればそれを使用する。
     * そうでない場合はpurchase_credential_file_pathに指定されているファイルのJSON文字列を使用する。
     *
     * @return array<mixed>
     */
    private function getPurchaseCredentialJsonArray(): array
    {
        $envkey = Config::get('wp_currency.store.googleplay_store.purchase_credential_env_key');
        $credentialJson = env($envkey, '');
        if ($credentialJson !== '') {
            return json_decode($credentialJson, true);
        }

        $credentialFile = Config::get('wp_currency.store.googleplay_store.purchase_credential_file_path');
        $credentialJson = file_get_contents($credentialFile);
        return json_decode($credentialJson, true);
    }

    /**
     * ストアから取得した公開鍵を取得する
     *
     * pubkey_env_keyに指定されてる環境変数が空でなければそれを使用する。
     * そうでなければ、pubkeyに指定されたファイルを読み込む。
     *
     * ※ストアにある公開鍵はRSAの文字列のみなので、ファイルにする場合は
     * 「-----BEGIN PUBLIC KEY-----」と「-----END PUBLIC KEY-----」の行で囲む必要がある
     *
     * @return string
     */
    private function getPlayStorePubKey(): string
    {
        // 環境変数から取得できた場合はそれを返す
        $envkey = Config::get('wp_currency.store.googleplay_store.pubkey_env_key');
        $pubkeyFile = env($envkey, '');

        // $pubkeyFileが空の場合はファイルから取得
        /** @var string|null $pubkeyFile */
        if ($pubkeyFile === '' || is_null($pubkeyFile)) {
            $pubkeyFilePath = Config::get('wp_currency.store.googleplay_store.pubkey');
            $pubkeyFile = file_get_contents($pubkeyFilePath);
        }

        // フォーマット
        $pubkeyFile = $this->formatPlayStorePubKey($pubkeyFile);

        return $pubkeyFile;
    }

    /**
     * 公開鍵のフォーマットを整える
     *
     * 環境変数から入力される文字列が公開鍵として認識されるよう整える。
     * たとえば改行(\n)が文字として解釈されているため、ここで改行に変換する
     *
     * @param string $pubkey
     * @return string
     */
    private function formatPlayStorePubKey(string $pubkey): string
    {
        // 前後の空白などを取り除く
        $pubkey = trim($pubkey);

        // \nが含まれていたら、改行コードに変換する
        // 念の為、-----の前後であることも固定する
        $pubkey = str_replace('-----\n', "-----\n", $pubkey);
        $pubkey = str_replace('\n-----', "\n-----", $pubkey);

        // BEGIN SSH2 PUBLIC KEYのようにBEGINとPUBLIC KEYの間に形式が入っている場合もあるため、正規表現で固定する
        // -----BEGIN PUBLIC KEY-----の後に改行がない場合、後ろに改行を追加する
        if (!preg_match('/-----BEGIN.*?-----\n/', $pubkey)) {
            $pubkey = preg_replace('/(-----BEGIN.*?-----)/', "$0\n", $pubkey);
        }
        // -----END PUBLIC KEY-----の前に改行がない場合、正規表現で追加する
        if (!preg_match('/\n-----END.*?-----/', $pubkey)) {
            $pubkey = preg_replace('/(-----END.*?-----)/', "\n$0", $pubkey);
        }

        // -----BEGIN PUBLIC KEY-----がない場合は追加する
        if (!preg_match('/-----BEGIN.*?-----/', $pubkey)) {
            $pubkey = "-----BEGIN PUBLIC KEY-----\n" . $pubkey;
        }
        // -----END PUBLIC KEY-----がない場合は追加する
        if (!preg_match('/-----END.*?-----/', $pubkey)) {
            $pubkey = $pubkey . "\n-----END PUBLIC KEY-----";
        }

        // 改行から改行の間にスペースがある場合、スペースを削除する
        $pubkey = preg_replace('/-----\n\s+/', "-----\n", $pubkey);
        $pubkey = preg_replace('/\s+\n-----/', "\n-----", $pubkey);

        return $pubkey;
    }

    /**
     * アイテムの購入を承認
     *
     * GooglePlay Billing Clinet 2.0では、商品を購入した後の承認が必要になった。
     *
     * これを行わない場合、3日後にGooglePlayプラットフォーム側で自動的に返金処理される。
     *
     * ※acknowledgementStateの遷移について
     * テストアカウントでの確認ではあるが、acknowledgeを行わない状態でも
     * クライアント(Unity IAP)での処理が終わった後のレシートのステータスは
     *
     *   [acknowledgementState] => 1
     *   [consumptionState] => 1
     *
     * となっていたため、acknowledgeを行わない場合でも問題ないと思われる。
     *
     * サーバーでエラーが発生するなどで、クライアント側での処理が終了しなかった場合、
     * ステータスは次のようになる。
     *
     *   [acknowledgementState] => 0
     *   [consumptionState] => 0
     *
     * このときacknowledgeを行うと、次のようにacknowledgementStateが変化する。
     *
     *   [acknowledgementState] => 1
     *   [consumptionState] => 0
     *
     * レストア処理によってクライアント側の処理も完了すると、consumptionStateも変化する。
     *
     *   [acknowledgementState] => 1
     *   [consumptionState] => 1
     *
     * @param string $receiptJson
     * @return void
     * @throws \Google\Exception
     *
     * @see https://developer.android.com/google/play/billing/integrate?hl=ja
     * @see https://developers.google.com/android-publisher/api-ref/rest/v3/purchases.products/acknowledge?hl=ja
     */
    public function purchaseAcknowledge(string $receiptJson)
    {
        $receipt = json_decode($receiptJson, true);

        $client = new GoogleClient();
        $scopes = [AndroidPublisher::ANDROIDPUBLISHER];
        $client->setScopes($scopes);
        $client->setAuthConfig($this->getPurchaseCredentialJsonArray());
        $service = new AndroidPublisher($client);

        $productPurchase = $service->purchases_products->get(
            $this->getAndroidPackageName(),
            $receipt['productId'],
            $receipt['purchaseToken']
        );

        if ($productPurchase->getAcknowledgementState() === self::ACKNOWLEDGEMENT_STATE_ACKNOWLEDGED) {
            // 承認済みなので何もしない
            return;
        }

        $postBody = new ProductPurchasesAcknowledgeRequest();
        $postBody->setDeveloperPayload($productPurchase->getDeveloperPayload());
        $service->purchases_products->acknowledge(
            $this->getAndroidPackageName(),
            $receipt['productId'],
            $receipt['purchaseToken'],
            $postBody
        );
    }
}
