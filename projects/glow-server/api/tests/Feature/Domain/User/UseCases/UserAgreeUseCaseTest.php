<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\User\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\User\UseCases\UserAgreeUseCase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class UserAgreeUseCaseTest extends TestCase
{
    private UserAgreeUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(UserAgreeUseCase::class);
    }

    public function test_exec_バージョン情報を更新できる()
    {
        // Setup
        $usrUser = $this->createUsrUser([
            'tos_version' => 1,
            'privacy_policy_version' => 1,
            'global_consent_version' => 1,
            'iaa_version' => 0,
        ]);
        $user = new CurrentUser($usrUser->getId());

        // 設定値を上書き
        Config::set('policies.tos.version', 2);
        Config::set('policies.privacy_policy.version', 3);
        Config::set('policies.global_consent.version', 2);
        Config::set('policies.tos.urls.ja', 'https://example.com/tos');
        Config::set('policies.privacy_policy.urls.ja', 'https://example.com/privacy');
        Config::set('policies.global_consent.urls.ja', 'https://example.com/consent');
        Config::set('policies.iaa.version', 1);
        Config::set('policies.iaa.urls.ja', 'https://example.com/iaa');

        // Exercise
        $this->useCase->exec($user, 2, 3, 2, 1, 'ja');

        // Verify
        // データベースの値が更新されていることを確認
        $this->assertDatabaseHas('usr_users', [
            'id' => $usrUser->getId(),
            'tos_version' => 2,
            'privacy_policy_version' => 3,
            'global_consent_version' => 2,
            'iaa_version' => 1,
        ]);
    }

    public function test_exec_設定とは異なるバージョンを送信した場合はエラーになる()
    {
        // Setup
        $usrUser = $this->createUsrUser([
            'tos_version' => 1,
            'privacy_policy_version' => 1,
            'global_consent_version' => 1,
            'iaa_version' => 1,
        ]);
        $user = new CurrentUser($usrUser->getId());

        // 設定値を上書き
        Config::set('policies.tos.version', 2);
        Config::set('policies.privacy_policy.version', 2);
        Config::set('policies.global_consent.version', 2);
        Config::set('policies.iaa.version', 2);
        Config::set('policies.tos.urls.ja', 'https://example.com/tos');
        Config::set('policies.privacy_policy.urls.ja', 'https://example.com/privacy');
        Config::set('policies.global_consent.urls.ja', 'https://example.com/consent');
        Config::set('policies.iaa.urls.ja', 'https://example.com/iaa');

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::VALIDATION_ERROR);

        // Exercise - 設定とは異なるバージョンを送信
        $this->useCase->exec($user, 3, 2, 2, 2, 'ja');
    }
}
