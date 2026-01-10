<?php

namespace WonderPlanet\Entity;

use Illuminate\Support\Facades\Config;

/**
 * マスターの暗号化に関する設定
 * 
 * createメソッドについては本来、
 * 設定方式を決めるのはプロダクトごとの都合になるため、
 * ライブラリ側に追加するメソッドではないかもしれないが、
 * 既存のcreateがここにあるので、こちらに実装する。
 *
 */
class MasterEncryptionSetting
{
    /**
     * コンストラクタ
     *
     * @param boolean $createEncryptedOnly
     * @param string $password
     * @param string $salt
     * @param boolean $enableEncrypted 暗号化を有効化するかどうか
     */
    public function __construct(
        private bool $createEncryptedOnly,
        private string $password,
        private string $salt,
        private bool $enableEncrypted = true,
    ) {}

    /**
     * リリースキーとハッシュから暗号化の設定を生成する
     * 
     * VQなどで使用されている形式になる。
     *
     * @param integer $releaseKey
     * @param string $hash
     * @return MasterEncryptionSetting
     */
    public static function create(int $releaseKey, string $hash): MasterEncryptionSetting
    {
        // 本番環境は暗号化したマスターのみ生成する
        $createEncryptedOnly = Config::get('app.env', "dev") === Config::get('wp_encryption.production_env_name');

        // パスワードはAPIの暗号化と同じものを使用する
        $password = Config::get('wp_encryption.password');

        // マスターバージョンをハッシュ化したものをソルトとして使用する
        $version = (string)$releaseKey . '_' . $hash;
        $salt = hash('sha256', $version);

        // こちらは以前と挙動を合わせるため暗号化を有効化で固定する
        return new MasterEncryptionSetting($createEncryptedOnly, $password, $salt, true);
    }

    /**
     * ハッシュから暗号化の設定を生成する
     * 
     * SEEDなどで使用されている形式になる。
     * 
     * @param string $hash
     * @return MasterEncryptionSetting
     */
    public static function createUsingHash(string $hash): MasterEncryptionSetting
    {
        // 本番環境は暗号化したマスターのみ生成する
        $createEncryptedOnly = Config::get('app.env', "dev") === Config::get('wp_encryption.production_env_name');

        // デバッグフラグがついていない場合は暗号化のみを生成する
        // appによるProduction環境を検出されたら暗号化のみを生成する
        if (
            !app()->hasDebugModeEnabled() ||
            app()->isProduction()
        ) {
            $createEncryptedOnly = true;
        }

        // 暗号化を有効化するかどうか
        $enableEncrypted = Config::get('wp_encryption.master_data_enabled');

        // マスタデータの暗号化パスワードを取得する
        $password = Config::get('wp_encryption.master_data_password');

        // $hashをハッシュ化したものをソルトとして使用する
        $salt = hash('sha256', $hash);

        return new MasterEncryptionSetting($createEncryptedOnly, $password, $salt, $enableEncrypted);
    }

    /**
     * 暗号化のみを生成するかどうかを取得
     * 
     * trueの場合、暗号化ファイルのみを生成する
     * falseの場合、暗号化ファイルと非暗号化ファイルのどちらも生成できる
     *
     * @return boolean
     */
    public function createEncryptedOnly(): bool
    {
        return $this->createEncryptedOnly;
    }

    /**
     * 暗号化パスワードを取得
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * ソルトを取得
     *
     * @return string
     */
    public function getSalt(): string
    {
        return $this->salt;
    }

    /**
     * 暗号化を有効化するかどうかを取得
     * 
     * trueの場合、ファイル暗号化する
     * falseの場合、ファイル暗号化しない
     *
     * @return boolean
     */
    public function enableEncrypted(): bool
    {
        // createEncryptedOnlyがtrueの場合、暗号化を有効化で固定する
        if ($this->createEncryptedOnly()) {
            return true;
        }
        return $this->enableEncrypted;
    }
}
