<?php

namespace App\Operators;

use App\Services\ConfigGetService;
use Aws\CloudFront\CloudFrontClient;

class CloudFrontOperator
{
    public function __construct(
        private ConfigGetService $configGetService
    ) {
    }

    private function deleteCache(array $config, array $paths): void
    {
        $credentials = [
            'key' => $config['key'],
            'secret' => $config['secret'],
        ];

        $client = new CloudFrontClient([
            'version' => 'latest',
            'region' => $config['region'],
            'credentials' => $credentials,
        ]);

        if (empty($paths)) {
            $paths = ['/*'];
        } else {
            // スラッシュで始まっていない場合は追加
            $paths = array_map(function ($path) {
                return $path[0] === '/' ? $path : '/' . $path;
            }, $paths);
        }

        try {
            $client->createInvalidation([
                'DistributionId' => $config['cloudfront_distribution_id'],
                'InvalidationBatch' => [
                    'Paths' => [
                        'Quantity' => count($paths),
                        'Items' => $paths,
                    ],
                    'CallerReference' => time(),
                ],
            ]);
        } catch (\Exception $exception) {
            throw new \Exception(
                sprintf(
                    'Error clearing CloudFront cache: %s',
                    $exception,
                )
            );
        }
    }

    public function deleteEnvFileCache(array $paths = []): void
    {
        $this->deleteCache($this->configGetService->getS3EnvFile(), $paths);
    }

    public function deleteS3WebviewCache(array $paths = []): void
    {
        $this->deleteCache($this->configGetService->getS3Webview(), $paths);
    }

    public function deleteS3InformationCache(array $paths = []): void
    {
        $this->deleteCache($this->configGetService->getS3Information(), $paths);
    }

    public function deleteS3BannerCache(array $paths = []): void
    {
        $this->deleteCache($this->configGetService->getS3Banner(), $paths);
    }

    /**
     * バケット名に応じてCloudFrontキャッシュを削除する
     *
     * @param string $bucket バケット名
     * @param array $paths キャッシュ削除対象のパス配列
     */
    public function deleteCacheByBucket(string $bucket, array $paths = []): void
    {
        $config = $this->configGetService->getS3ConfigByBucket($bucket);
        if ($config === null) {
            // 該当バケットの設定がない場合は何もしない
            return;
        }

        $this->deleteCache($config, $paths);
    }
}
