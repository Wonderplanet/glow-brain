<?php

declare(strict_types=1);

namespace App\Services;

/**
 * 環境に関するサービス
 */
class EnvironmentService
{
    public function __construct(
    ) {
    }

    /**
     * アプリケーションID（SVID）
     * @return string
     */
    public function getApplicationId(): string
    {
        return env('BNE_APPLICATION_ID', '');
    }

    /**
     * バンダイナムコのクライアントID
     * @return string
     */
    public function getClientId(): string
    {
        return env('BNE_CLIENT_ID', '');
    }

    /**
     * バンダイナムコのクライアントシークレット
     * @return string
     */
    public function getClientSecret(): string
    {
        return env('BNE_CLIENT_SECRET', '');
    }

    /**
     * VoidedPurchaseデータが格納されているS3のリージョン
     * @return string
     */
    public function getBneVoidedPurchaseS3Region(): string
    {
        return env('BNE_VOIDED_PURCHASE_S3_REGION', '');
    }

    /**
     * VoidedPurchaseデータが格納されているAWSのアクセスキー
     * @return string
     */
    public function getBneVoidedPurchaseAwsAccessKey(): string
    {
        return env('BNE_VOIDED_PURCHASE_AWS_ACCESS_KEY', '');
    }

    /**
     * VoidedPurchaseデータが格納されているAWSのシークレットキー
     * @return string
     */
    public function getBneVoidedPurchaseAwsSecretAccessKey(): string
    {
        return env('BNE_VOIDED_PURCHASE_AWS_SECRET_ACCESS_KEY', '');
    }

    /**
     * VoidedPurchaseデータが格納されているS3のバケット名
     * @return string
     */
    public function getBneVoidedPurchaseS3Bucket(): string
    {
        return env('BNE_VOIDED_PURCHASE_S3_BUCKET', '');
    }

    /**
     * VoidedPurchaseデータが格納されているS3のパスに含めるAndroidのパッケージ名
     * @return string
     */
    public function getPackageName(): string
    {
        return env('PACKAGE_NAME', '');
    }

    /**
     * VoidedPurchaseデータが格納されているS3のパスに含めるタイトルID
     * @return string
     */
    public function getBneTitleId(): string
    {
        return env('BNE_TITLE_ID', '');
    }
}
