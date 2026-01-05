<?php

namespace App\Services;

use Aws\Credentials\CredentialProvider;

class AwsCredentialProvider
{
    /**
     * ECS または EC2 にアタッチされたロール専用のクレデンシャルプロバイダを取得
     *
     * @return callable
     */
    public static function getRoleBasedCredentialProvider(): callable
    {
        $provider = CredentialProvider::chain(
            CredentialProvider::ecsCredentials(),       // ECSタスクロール用
            CredentialProvider::instanceProfile()       // EC2インスタンスロール用（IMDS）
        );

        return CredentialProvider::memoize($provider); // キャッシュ有効化
    }
}
