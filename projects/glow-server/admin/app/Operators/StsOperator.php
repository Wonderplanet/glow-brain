<?php

namespace App\Operators;

use Aws\Sts\StsClient;
use Aws\Credentials\CredentialProvider;
use Aws\Credentials\AssumeRoleCredentialProvider;

class StsOperator
{

    public function getTemporaryCredentials(string $roleArn): callable
    {
        $config = config('filesystems.disks.s3');
        try {
            $stsClient = new StsClient([
                'version' => 'latest',
                'region' => 'ap-northeast-1',
                'credentials' => [
                    'key' => $config['key'],
                    'secret' => $config['secret'],
                ],
            ]);

            $assumeRoleCredentials = new AssumeRoleCredentialProvider([
                'client' => $stsClient,
                'assume_role_params' => [
                    'RoleArn' => $roleArn,
                    'RoleSessionName' => 'session_name',
                ],
            ]);
            $provider = CredentialProvider::memoize($assumeRoleCredentials);
        } catch (\Exception $exception) {
            throw new \Exception('Error getting temporary token : ' . $exception);
        }
        return $provider;
    }
}
