<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Admin\Operators\S3Operator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\SerializedDataFileOperator;
use WonderPlanet\Util\Cryptography\AesRequestEncryptor;

class EnvSettingService
{
    public function __construct(
        private readonly SerializedDataFileOperator $fileOperator
    ) {
    }

    public function encryptEnvFile(string $envFileName, string $clientVersionHash, string $encryptHash): string
    {
        $filePath = resource_path('json') . '/' . $envFileName;
        $outputFileName = $clientVersionHash . '.data';
        $serializedFileDirPath = Config::get('admin.envSerializedFileDir');
        $serializedFileDirectory = $serializedFileDirPath . '/' . $clientVersionHash . '/';
        $encryptedFilePath = $serializedFileDirectory . $outputFileName;
        $password = Config::get('wp_encryption.env_data_password');

        $cryptography = new AesRequestEncryptor();
        $fileData = file_get_contents($filePath);
        $encryptedData = $cryptography->encrypt($fileData, $password, $encryptHash);

        $this->fileOperator->write($encryptedFilePath, '',base64_encode($encryptedData));

        return $serializedFileDirectory;
    }

    public function uploadS3(string $filePath, string $clientVersionHash): void
    {
        $s3 = new S3Operator();
        $s3->uploadDirectory($filePath, 's3_env_file', 'env/');
    }

    public function decryptEnvFile(string $fileContent, string $clientVersionHash): array
    {
        $password = Config::get('wp_encryption.env_data_password');
        $cryptography = new AesRequestEncryptor();
        $hashParts = explode('_', $clientVersionHash);
        $hash = end($hashParts);
        $response = $cryptography->decrypt(base64_decode($fileContent), $password, $hash);
        $json = $response ?? '';
        return json_decode($json, true)['environments'][0] ?? [];
    }
}
