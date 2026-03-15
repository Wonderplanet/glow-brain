<?php

declare(strict_types=1);

namespace App\Services;

class ConfigGetService
{
    public function __construct(
    ) {
    }

    /**
     * 設定値を取得する。
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function get($key, $default = null)
    {
        return config($key, $default);
    }

    /**
     * System
     */

    public function getTimezone()
    {
        return $this->get('app.timezone');
    }

    public function getLocale()
    {
        return $this->get('app.locale');
    }

    /**
     * Admin Config
     */

    public function getAdminInformationDir()
    {
        return $this->get('admin.informationDir');
    }

    public function getAdminGachaCautionDir()
    {
        return $this->get('admin.gachaCautionDir');
    }

    public function getAdminJumpPlusRewardDir()
    {
        return $this->get('admin.jumpPlusRewardDir');
    }

    public function getAdminAppId()
    {
        // ガシャ事後検証で送信するjsonに含めるappId
        return $this->get('admin.appId');
    }

    public function getAdminAppName()
    {
        // ガシャ事後検証で送信するjsonに含めるappName
        return $this->get('admin.appName');
    }

    public function getSourceEnvList()
    {
        return $this->get('admin.sourceEnvList.' . config('app.env'), []);
    }

    public function getAdminApiDomain(string $environment): ?string
    {
        return $this->get('admin.adminApiDomain.' . $environment);
    }

    /**
     * AWS
     */

    /**
     * 環境名から、どのAWSアカウントに属するかを取得
     * @param ?string $env null: 未設定
     */
    public function getAwsAccountByEnv(string $env): ?string
    {
        return $this->get('services.aws_account.' . $env);
    }

    public function getSelfAwsAccount(): string
    {
        $awsAccount = $this->get('services.aws_account.' . config('app.env'));
        if ($awsAccount === null) {
            throw new \Exception("AWSアカウントが設定されていません: " . config('app.env'));
        }
        return $awsAccount;
    }

    /**
     * 環境名を指定して、同じawsアカウントかどうかを判定するメソッド
     * @param string $targetEnv
     * @return bool
     */
    public function isSameAwsAccount(string $targetEnv): bool
    {
        $fromAwsAccount = $this->getAwsAccountByEnv($targetEnv);
        $toAwsAccount = $this->getSelfAwsAccount();
        return $fromAwsAccount === $toAwsAccount;
    }

    public function getAthenaConfig(): array
    {
        return $this->get('services.athena');
    }

    /**
     * S3
     */

    public function getS3MasterBucket()
    {
        return $this->get('filesystems.disks.s3.bucket');
    }

    public function getS3BannerBucket(): string
    {
        return $this->get('filesystems.disks.s3_banner.bucket');
    }

    public function getS3BannerUrl()
    {
        return $this->get('filesystems.disks.s3_banner.url');
    }

    public function getS3Banner(): array
    {
        return $this->get('filesystems.disks.s3_banner');
    }

    /**
     * バケット名に対応するS3設定を取得する
     *
     * @param string $bucket バケット名
     * @return array|null 設定配列、見つからない場合はnull
     */
    public function getS3ConfigByBucket(string $bucket): ?array
    {
        // 各バケットの設定をチェック
        if ($bucket === $this->getS3BannerBucket()) {
            return $this->getS3Banner();
        }

        if ($bucket === $this->getS3InformationBucket()) {
            return $this->getS3Information();
        }

        if ($bucket === $this->getS3WebviewBucket()) {
            return $this->getS3Webview();
        }

        return null;
    }

    public function getS3Information(): array
    {
        return $this->get('filesystems.disks.s3_information');
    }

    public function getS3InformationBucket()
    {
        return $this->get('filesystems.disks.s3_information.bucket');
    }

    public function getS3InformationUrl()
    {
        return $this->get('filesystems.disks.s3_information.url');
    }

    public function getS3EnvFile()
    {
        return $this->get('filesystems.disks.s3_env_file');
    }

    public function getS3Webview(): array
    {
        return $this->get('filesystems.disks.s3_webview');
    }

    public function getS3WebviewBucket()
    {
        return $this->get('filesystems.disks.s3_webview.bucket');
    }

    public function getS3WebviewUrl()
    {
        return $this->get('filesystems.disks.s3_webview.url');
    }

    public function getS3WebviewCloudfrontDistributionId()
    {
        return $this->get('filesystems.disks.s3_webview.cloudfront_distribution_id');
    }

    public function getS3Asset(): array
    {
        return $this->get('filesystems.disks.s3_asset');
    }

    public function getS3AssetBucket()
    {
        return $this->get('filesystems.disks.s3_asset.bucket');
    }

    public function getS3AssetUrl()
    {
        return $this->get('filesystems.disks.s3_asset.url');
    }


    public function getS3AdminBucket()
    {
        return $this->getS3SourceAdminBucket(config('app.env'));
    }

    /**
     * @return array<mixed>
     */
    public function getS3Source(): array
    {
        return $this->get('filesystems.disks.s3_source');
    }

    public function getS3SourceBannerBucket(string $env)
    {
        return $this->get('filesystems.disks.s3_source.' . $env . '.banner_bucket');
    }

    public function getS3SourceWebviewBucket(string $env)
    {
        return $this->get('filesystems.disks.s3_source.' . $env . '.webview_bucket');
    }

    public function getS3SourceInformationBucket(string $env)
    {
        return $this->get('filesystems.disks.s3_source.' . $env . '.information_bucket');
    }

    public function getS3SourceAdminBucket(string $env)
    {
        return $this->get('filesystems.disks.s3_source.' . $env . '.admin_bucket');
    }

    /**
     * 昇格元環境のバケットと同様の役割を持つ、昇格先のバケット名を取得
     * 例：
     *   昇格元: dev-ld, バケット名: glow-dev-ld-banner
     *   昇格先: qa, バケット名: glow-qa-banner
     * @return string|null バケット名。null=該当なし
     */
    public function getBucketBySourceEnvAndBucket(string $sourceEnv, string $sourceBucket): ?string
    {
        $s3Source = $this->getS3Source();

        $sourceBucketRoleKey = array_search($sourceBucket, $s3Source[$sourceEnv] ?? [], true);
        if ($sourceBucketRoleKey === false) {
            return null;
        }

        $selfEnvS3Source = $s3Source[config('app.env')] ?? [];
        return $selfEnvS3Source[$sourceBucketRoleKey] ?? null;
    }

    /**
     * Google
     */

    public function getGoogleCredentialPath()
    {
        return $this->get('admin.googleCredentialPath');
    }

    public function getGoogleSpreadSheetListSheetId()
    {
        return $this->get('admin.googleSpreadSheetListSheetId');
    }

    /**
     * DynamoDB
     */

    public function getDynamoDbMaintenance()
    {
        return $this->get('services.dynamodb_maintenance');
    }

    /**
     * EventBridge
     */

    public function getEventBridgeStartSchedulerMaintenance()
    {
        return $this->get('services.eventbridge_start_scheduler_maintenance');
    }

    public function getEventBridgeEndSchedulerMaintenance()
    {
        return $this->get('services.eventbridge_end_scheduler_maintenance');
    }

    /**
     * Lambda
     */

    public function getLambdaMaintenance()
    {
        return $this->get('services.lambda_maintenance');
    }

    public function getLambdaJumpPlusReward()
    {
        return $this->get('services.lambda_jump_plus_reward');
    }

    /**
     * WPデータレイク用S3設定を取得
     */
    public function getS3WpDatalake(): array
    {
        return $this->get('filesystems.disks.s3_wp_datalake');
    }

    public function getS3WpDatalakeBucket(): ?string
    {
        return $this->get('filesystems.disks.s3_wp_datalake.bucket');
    }
}
