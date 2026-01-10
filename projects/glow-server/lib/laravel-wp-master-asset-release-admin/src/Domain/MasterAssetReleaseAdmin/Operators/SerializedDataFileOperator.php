<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators;

use Illuminate\Filesystem\Filesystem;
use MessagePack\Packer;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\MessagePackTransformers\CarbonTransformer;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\MessagePackTransformers\EnumTransformer;
use WonderPlanet\Entity\MasterEncryptionSetting;
use WonderPlanet\Util\Cryptography\AesRequestEncryptor;

class SerializedDataFileOperator
{
    /**
     * コンストラクタ
     *
     * lib-laravel-wp-encryptionのEncryptorをDIする
     * ライブラリがドメインに分かれていないので、直接使用している
     * 
     * @param AesRequestEncryptor $encryptor
     */
    public function __construct(
        private readonly AesRequestEncryptor $encryptor
    ) {}

    public function cleanup($path): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->deleteDirectory($path);
    }

    /**
     * MasterDataのファイルをjson, messagePack形式でS3に書き込む
     * @param string $jsonPath
     * @param string $messagePackPath
     * @param array|string $data
     * @return void
     */
    public function write(string $jsonPath, string $messagePackPath, array|string $data): void
    {
        // 書き込み先にディレクトリがなかったら作っておく
        $directoryPath = dirname($jsonPath);
        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0755, true); // recursive
        }

        if (is_array($data)) {
            $this->writeAsJson($jsonPath, $data);
        } else {
            file_put_contents($jsonPath, $data);
        }
    }

    /**
     * json形式のファイルを生成しパスの場所にファイルを置く
     * @param string $path
     * @param array $data
     * @return void
     */
    private function writeAsJson(string $path, array $data): void
    {
        // jsonファイルを作成してput
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        file_put_contents($path, $json);
    }

    /**
     * messagePack形式にファイルを変換しパスの場所にファイルを置く
     * @param string $path
     * @param array|string $data
     * @return void
     */
    private function writeAsMessagePack(string $path, array|string $data): void
    {
        // ENUM型、Carbon型をpack出来るようにtransformerを追加する
        $packer = new Packer(null, [new EnumTransformer(), new CarbonTransformer()]);
        // 配列/stringデータをpackする
        $msgPack = $packer->pack($data);
        // messagePack形式のバイナリファイルを配置する
        file_put_contents($path, $msgPack);
    }

    /**
     * MasterDataのファイルを圧縮・暗号化する
     *
     * 暗号化にはlaravel-wp-encryptionのEncryptorを使用する
     * 指定した$pathのファイルが暗号化され、置き換えられる
     * 
     * $pathを上書きしたい場合、$outputに$pathを指定する
     * 
     * @param string $path
     * @param string $output
     * @param MasterEncryptionSetting $setting
     * @return void
     */
    public function gzipAndEncryptMasterdataFile(string $path, string $output, MasterEncryptionSetting $setting): void
    {
        $data = file_get_contents($path);

        // 圧縮
        // 最高圧縮率で圧縮
        $compressedData = gzencode($data, 9);

        // 暗号化
        $encryptedData = $this->encryptor->encrypt($compressedData, $setting->getPassword(), $setting->getSalt());

        if ($encryptedData === false) {
            throw new \Exception('Failed to encrypt data');
        }

        // 一時ファイルに出力
        $tempPath = tmpfile();
        fwrite($tempPath, $encryptedData);

        // 出力し終わったら一時ファイルを$pathに移動
        // ファイルが存在していたら削除する
        if (file_exists($output)) {
            unlink($output);
        }
        rename(stream_get_meta_data($tempPath)['uri'], $output);
    }

    /**
     * MasterDataのファイルを復号・解凍する
     *
     * @param string $path
     * @param string $output
     * @param MasterEncryptionSetting $setting
     * @return void
     */
    public function decryptAndDecompressMasterdataFile(string $path, string $output, MasterEncryptionSetting $setting): void
    {
        $data = file_get_contents($path);

        // 復号
        $decryptedData = $this->encryptor->decrypt($data, $setting->getPassword(), $setting->getSalt());

        if ($decryptedData === false) {
            throw new \Exception('Failed to decrypt data');
        }

        // 解凍
        $uncompressedData = gzdecode($decryptedData);

        // 一時ファイルに出力
        $tempPath = tmpfile();
        fwrite($tempPath, $uncompressedData);

        // 出力し終わったら一時ファイルを$pathに移動
        // ファイルが存在していたら削除する
        if (file_exists($output)) {
            unlink($output);
        }

        rename(stream_get_meta_data($tempPath)['uri'], $output);
    }
}
