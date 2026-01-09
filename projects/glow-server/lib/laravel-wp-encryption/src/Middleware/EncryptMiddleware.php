<?php

namespace WonderPlanet\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use WonderPlanet\Entity\ApiEncryptionSetting;
use WonderPlanet\Util\Cryptography\AesRequestEncryptor;

/**
 * リクエスト・レスポンスのボディを復号・暗号化するミドルウェア
 */
class EncryptMiddleware
{
    protected AesRequestEncryptor $cryptography;

    public function __construct()
    {
        $this->cryptography = new AesRequestEncryptor();
    }

    public function handle(Request $request, Closure $next)
    {
        $apiEncryptionSetting = ApiEncryptionSetting::create($request);

        // ライブラリを使用する側でも暗号化設定を参照できるようにする
        App::instance(ApiEncryptionSetting::class, $apiEncryptionSetting);

        if (!$apiEncryptionSetting->enableEncryption()) {
            $response = $next($request);
            $response->header(config('wp_encryption.response_encrypted_header'), 'false');
            return $response;
        }

        // リクエストを復号して置き換える
        $requestPassword = $apiEncryptionSetting->getRequestPassword();
        $salt = $apiEncryptionSetting->getSalt();

        $content = $request->getContent();
        if (isset($content)) {
            $content = $this->cryptography->decrypt($content, $requestPassword, $salt);
            if (isset($content)) {
                $content = json_decode($content, true);
                if (isset($content)){
                    $request->replace($content);
                }
            }
        }

        $response = $next($request);

        // 暗号化実施
        $responsePassword = $apiEncryptionSetting->getResponsePassword();
        $response->setContent($this->cryptography->encrypt($response->getContent(), $responsePassword, $salt));
        $response->header(config('wp_encryption.response_encrypted_header'), 'true');

        return $response;
    }
}
