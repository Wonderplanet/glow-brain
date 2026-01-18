<?php

declare(strict_types=1);

namespace App\Domain\Common\Services;

class LegalDocumentService
{
    /**
     * configに設定されている利用規約情報を取得
     *
     * @param string $language
     * @return array{
     *   version: int,
     *   url: string
     * }
     */
    public function getTosInfo(string $language): array
    {
        $url = config("policies.tos.urls.{$language}");
        if (is_null($url)) {
            $defaultLang = config('policies.tos.default_url');
            $url = config("policies.tos.urls.{$defaultLang}");
        }

        return [
            'version' => config('policies.tos.version'),
            'url' => $url,
        ];
    }

    /**
     * configに設定されているプライバシーポリシー情報を取得
     *
     * @param string $language
     * @return array{
     *   version: int,
     *   url: string
     * }
     */
    public function getPrivacyPolicyInfo(string $language): array
    {
        $url = config("policies.privacy_policy.urls.{$language}");
        if (is_null($url)) {
            $defaultLang = config('policies.privacy_policy.default_url');
            $url = config("policies.privacy_policy.urls.{$defaultLang}");
        }

        return [
            'version' => config('policies.privacy_policy.version'),
            'url' => $url,
        ];
    }

    /**
     * configに設定されているグローバルコンセント情報を取得
     *
     * @param string $language
     * @return array{
     *   version: int,
     *   url: string
     * }
     */
    public function getGlobalConsentInfo(string $language): array
    {
        $url = config("policies.global_consent.urls.{$language}");
        if (is_null($url)) {
            $defaultLang = config('policies.global_consent.default_url');
            $url = config("policies.global_consent.urls.{$defaultLang}");
        }

        return [
            'version' => config('policies.global_consent.version'),
            'url' => $url,
        ];
    }

    /**
     * configに設定されているアプリ内広告ポリシー情報を取得
     *
     * @param string $language
     * @return array{
     *   version: int,
     *   url: string
     * }
     */
    public function getIaaInfo(string $language): array
    {
        $url = config("policies.in_app_advertisement.urls.{$language}");
        if (is_null($url)) {
            $defaultLang = config('policies.in_app_advertisement.default_url');
            $url = config("policies.in_app_advertisement.urls.{$defaultLang}");
        }

        return [
            'version' => config('policies.in_app_advertisement.version'),
            'url' => $url,
        ];
    }
}
