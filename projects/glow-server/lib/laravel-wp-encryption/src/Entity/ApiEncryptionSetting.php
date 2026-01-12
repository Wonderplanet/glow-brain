<?php

namespace WonderPlanet\Entity;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * APIの暗号化に関する設定
 */
class ApiEncryptionSetting
{
    public function __construct(
        private bool $enableEncryption,
        private ?string $requestPassword,
        private ?string $responsePassword,
        private ?string $salt,
    ) {
    }

    public static function create(Request $request): ApiEncryptionSetting
    {
        $disableHeader = $request->header(Config::get('wp_encryption.disable_header'));
        if ($disableHeader === null) {
            throw new GameException(ErrorCode::VALIDATION_ERROR);
        }

        $enableEncryption = Config::get('app.env', "dev") === Config::get('wp_encryption.production_env_name')
            || (Config::get('wp_encryption.enabled') && $disableHeader === 'true');
        $requestPassword = Config::get('wp_encryption.request_password');
        $responsePassword = Config::get('wp_encryption.response_password');
        $salt = $request->header(Config::get('wp_encryption.salt_header'));
        
        return new ApiEncryptionSetting($enableEncryption, $requestPassword, $responsePassword, $salt);
    }

    public function enableEncryption(): bool
    {
        return $this->enableEncryption;
    }

    public function getRequestPassword(): ?string
    {
        return $this->requestPassword;
    }

    public function getResponsePassword(): ?string
    {
        return $this->responsePassword;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }
}
