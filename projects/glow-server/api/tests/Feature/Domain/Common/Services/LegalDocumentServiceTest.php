<?php

namespace Feature\Domain\Common\Services;

use App\Domain\Common\Services\LegalDocumentService;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class LegalDocumentServiceTest extends TestCase
{
    private LegalDocumentService $legalDocumentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->legalDocumentService = $this->app->make(LegalDocumentService::class);
    }

    public function test_getTosInfo_設定した言語の利用規約情報を取得できる()
    {
        Config::set('policies.tos.version', 99);
        Config::set('policies.tos.urls.ja', 'policies/terms_of_service/tos_v1_ja.html');

        // Exercise
        $result = $this->legalDocumentService->getTosInfo('ja');

        // Verify
        $this->assertEquals(99, $result['version']);
        $this->assertEquals('policies/terms_of_service/tos_v1_ja.html', $result['url']);
    }

    public function test_getTosInfo_設定していない言語の場合はデフォルト言語の利用規約情報を取得できる()
    {
        Config::set('policies.tos.version', 99);
        Config::set('policies.tos.urls.ja', 'policies/terms_of_service/tos_v1_ja.html');
        Config::set('policies.tos.urls.en', 'policies/terms_of_service/tos_v1_en.html');
        Config::set('policies.tos.default_url', 'en');

        // Exercise
        $result = $this->legalDocumentService->getTosInfo('test');

        // Verify
        $this->assertEquals(99, $result['version']);
        $this->assertEquals('policies/terms_of_service/tos_v1_en.html', $result['url']);
    }

    public function test_getPrivacyPolicyInfo_設定した言語のプライバシーポリシー情報を取得できる()
    {
        Config::set('policies.privacy_policy.version', 88);
        Config::set('policies.privacy_policy.urls.ja', 'policies/privacy_policy/privacy_v1_ja.html');

        // Exercise
        $result = $this->legalDocumentService->getPrivacyPolicyInfo('ja');

        // Verify
        $this->assertEquals(88, $result['version']);
        $this->assertEquals('policies/privacy_policy/privacy_v1_ja.html', $result['url']);
    }

    public function test_getPrivacyPolicyInfo_設定していない言語の場合はデフォルト言語のプライバシーポリシー情報を取得できる()
    {
        Config::set('policies.privacy_policy.version', 88);
        Config::set('policies.privacy_policy.urls.ja', 'policies/privacy_policy/privacy_v1_ja.html');
        Config::set('policies.privacy_policy.urls.en', 'policies/privacy_policy/privacy_v1_en.html');
        Config::set('policies.privacy_policy.default_url', 'en');

        // Exercise
        $result = $this->legalDocumentService->getPrivacyPolicyInfo('test');

        // Verify
        $this->assertEquals(88, $result['version']);
        $this->assertEquals('policies/privacy_policy/privacy_v1_en.html', $result['url']);
    }

    public function test_getGlobalConsentInfo_設定した言語のグローバルコンセント情報を取得できる()
    {
        Config::set('policies.global_consent.version', 77);
        Config::set('policies.global_consent.urls.ja', 'policies/global_consent/consent_v1_ja.html');

        // Exercise
        $result = $this->legalDocumentService->getGlobalConsentInfo('ja');

        // Verify
        $this->assertEquals(77, $result['version']);
        $this->assertEquals('policies/global_consent/consent_v1_ja.html', $result['url']);
    }

    public function test_getGlobalConsentInfo_設定していない言語の場合はデフォルト言語のグローバルコンセント情報を取得できる()
    {
        Config::set('policies.global_consent.version', 77);
        Config::set('policies.global_consent.urls.ja', 'policies/global_consent/consent_v1_ja.html');
        Config::set('policies.global_consent.urls.en', 'policies/global_consent/consent_v1_en.html');
        Config::set('policies.global_consent.default_url', 'en');

        // Exercise
        $result = $this->legalDocumentService->getGlobalConsentInfo('test');

        // Verify
        $this->assertEquals(77, $result['version']);
        $this->assertEquals('policies/global_consent/consent_v1_en.html', $result['url']);
    }

    public function test_getIaaInfo_設定した言語のiaaポリシー情報を取得できる()
    {
        Config::set('policies.in_app_advertisement.version', 55);
        Config::set('policies.in_app_advertisement.urls.ja', 'policies/in_app_advertisement/ad_v1_ja.html');

        // Exercise
        $result = $this->legalDocumentService->getIaaInfo('ja');

        // Verify
        $this->assertEquals(55, $result['version']);
        $this->assertEquals('policies/in_app_advertisement/ad_v1_ja.html', $result['url']);
    }

    public function test_getIaaInfo_設定していない言語の場合はデフォルト言語のiaaポリシー情報を取得できる()
    {
        Config::set('policies.in_app_advertisement.version', 55);
        Config::set('policies.in_app_advertisement.urls.ja', 'policies/in_app_advertisement/ad_v1_ja.html');
        Config::set('policies.in_app_advertisement.urls.en', 'policies/in_app_advertisement/ad_v1_en.html');
        Config::set('policies.in_app_advertisement.default_url', 'en');

        // Exercise
        $result = $this->legalDocumentService->getIaaInfo('test');

        // Verify
        $this->assertEquals(55, $result['version']);
        $this->assertEquals('policies/in_app_advertisement/ad_v1_en.html', $result['url']);
    }
}
