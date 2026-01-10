<?php

namespace Tests\Feature\Http\Middleware;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Enums\UserStatus;
use App\Domain\Common\Exceptions\GameException;
use App\Http\Middleware\UserStatusCheck;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;

class UserStatusCheckTest extends TestCase
{
    private UserStatusCheck $userStatusCheck;

    public function setUp(): void
    {
        parent::setUp();
        $this->userStatusCheck = app(UserStatusCheck::class);
    }

    #[DataProvider('params_handle_通常プレイ可ユーザーの場合は通過する')]
    public function test_handle_通常プレイ可ユーザーのみ通過する(int $status, ?int $errorCode, ?string $suspendEndAt): void
    {
        // SetUp
        $usrUserId = 'user1';
        $response = 'next';

        $this->fixTime('2024-09-03 00:00:00');

        $user = new CurrentUser($usrUserId, status: $status, suspendEndAt: $suspendEndAt);

        /** @var Request $mockedRequest */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('user')->andReturn($user);
            $mock->shouldReceive('path')->andReturn('path');
        });
        $next = fn() => $response;

        if ($errorCode !== null) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }

        // Exercise
        $result = $this->userStatusCheck->handle($mockedRequest, $next);

        // Verify
        $this->assertEquals($response, $result);
    }

    public static function params_handle_通常プレイ可ユーザーの場合は通過する(): array
    {
        // 現在日時を'2024-09-03 00:00:00'に固定してテストケースを作成

        return [
            '通常プレイ可ユーザー 時限BAN日時未設定' => [UserStatus::NORMAL->value, null, NULL],
            '通常プレイ可ユーザー 時限BAN期間中' => [UserStatus::NORMAL->value, null, '2024-09-05 00:00:00'], // 実際には発生しない想定のケース

            '不正行為で、時限BAN中ユーザー 時限BAN日時未設定' => [UserStatus::BAN_TEMPORARY_CHEATING->value, ErrorCode::USER_ACCOUNT_BAN_TEMPORARY_BY_CHEATING, NULL],
            '不正行為で、時限BAN中ユーザー 時限BAN期間中' => [UserStatus::BAN_TEMPORARY_CHEATING->value, ErrorCode::USER_ACCOUNT_BAN_TEMPORARY_BY_CHEATING, '2024-09-05 00:00:00'],
            '不正行為で、時限BAN中ユーザー 時限BAN期間中(終了日時と同時刻)' => [UserStatus::BAN_TEMPORARY_CHEATING->value, ErrorCode::USER_ACCOUNT_BAN_TEMPORARY_BY_CHEATING, '2024-09-03 00:00:00'],
            '不正行為で、時限BAN中ユーザー 時限BAN期間後' => [UserStatus::BAN_TEMPORARY_CHEATING->value, null, '2024-09-01 00:00:00'],

            '異常データ検出で、時限BAN中ユーザー 時限BAN日時未設定' => [UserStatus::BAN_TEMPORARY_DETECTED_ANOMALY->value, ErrorCode::USER_ACCOUNT_BAN_TEMPORARY_BY_DETECTED_ANOMALY, NULL],
            '異常データ検出で、時限BAN中ユーザー 時限BAN期間中' => [UserStatus::BAN_TEMPORARY_DETECTED_ANOMALY->value, ErrorCode::USER_ACCOUNT_BAN_TEMPORARY_BY_DETECTED_ANOMALY, '2024-09-05 00:00:00'],
            '異常データ検出で、時限BAN中ユーザー 時限BAN期間中(終了日時と同時刻)' => [UserStatus::BAN_TEMPORARY_DETECTED_ANOMALY->value, ErrorCode::USER_ACCOUNT_BAN_TEMPORARY_BY_DETECTED_ANOMALY, '2024-09-03 00:00:00'],
            '異常データ検出で、時限BAN中ユーザー 時限BAN期間後' => [UserStatus::BAN_TEMPORARY_DETECTED_ANOMALY->value, null, '2024-09-01 00:00:00'],

            '永久BAN中ユーザー 時限BAN日時未設定' => [UserStatus::BAN_PERMANENT->value, ErrorCode::USER_ACCOUNT_BAN_PERMANENT, NULL],
            '永久BAN中ユーザー 時限BAN期間中' => [UserStatus::BAN_PERMANENT->value, ErrorCode::USER_ACCOUNT_BAN_PERMANENT, '2024-09-05 00:00:00'],
            '永久BAN中ユーザー 時限BAN期間後' => [UserStatus::BAN_PERMANENT->value, ErrorCode::USER_ACCOUNT_BAN_PERMANENT, '2024-09-01 00:00:00'],

            'アカウント削除済みユーザー 時限BAN日時未設定' => [UserStatus::DELETED->value, ErrorCode::USER_ACCOUNT_DELETED, NULL],
            'アカウント削除済みユーザー 時限BAN期間中' => [UserStatus::DELETED->value, ErrorCode::USER_ACCOUNT_DELETED, '2024-09-05 00:00:00'],
            'アカウント削除済みユーザー 時限BAN期間後' => [UserStatus::DELETED->value, ErrorCode::USER_ACCOUNT_DELETED, '2024-09-01 00:00:00'],

            '返金対応中ユーザー 時限BAN日時未設定' => [UserStatus::REFUNDING->value, ErrorCode::USER_ACCOUNT_REFUNDING, NULL],
            '返金対応中ユーザー 時限BAN期間中' => [UserStatus::REFUNDING->value, ErrorCode::USER_ACCOUNT_REFUNDING, '2024-09-05 00:00:00'],
            '返金対応中ユーザー 時限BAN期間後' => [UserStatus::REFUNDING->value, ErrorCode::USER_ACCOUNT_REFUNDING, '2024-09-01 00:00:00'],
        ];
    }
}
