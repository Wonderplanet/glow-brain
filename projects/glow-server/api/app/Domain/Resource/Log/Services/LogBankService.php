<?php

declare(strict_types=1);

namespace App\Domain\Resource\Log\Services;

use App\Domain\Common\Constants\System;
use App\Domain\Resource\Log\Enums\BankKPIF001EventId;
use App\Domain\Resource\Log\Repositories\LogBankRepository;
use App\Domain\User\Models\UsrUserInterface;
use Carbon\CarbonImmutable;

class LogBankService
{
    public function __construct(
        private LogBankRepository $logBankRepository,
    ) {
    }

    /**
     * BANK F001 の登録イベントとしてログを記録する
     *
     * @param string                $usrUserId
     * @param CarbonImmutable       $userCreatedAt
     * @param UsrUserInterface|null $recentlyUsrUser
     */
    public function createLogBankRegistered(
        string $usrUserId,
        CarbonImmutable $userCreatedAt,
        ?UsrUserInterface $recentlyUsrUser,
    ): void {
        $requestAt = $this->getRequestAt();
        if ($recentlyUsrUser !== null) {
            // リセマラユーザーに対してevent_id:200を送信
            $eventId = BankKPIF001EventId::USER_DISABLED->value;
            $this->createLogBank(
                $recentlyUsrUser->getUsrUserId(),
                $requestAt,
                $eventId,
                $recentlyUsrUser->getCreatedAt()
            );
        }
        // 新規ユーザーに対してevent_id:100を送信
        $eventId = BankKPIF001EventId::USER_REGISTERED->value;
        $this->createLogBank($usrUserId, $requestAt, $eventId, $userCreatedAt);
    }

    /**
     * BANK F001 の Activeイベントとしてログを記録する
     *
     * @param string $usrUserId
     * @param CarbonImmutable $usrGameStartAt
     */
    public function createLogBankActive(
        string $usrUserId,
        CarbonImmutable $usrGameStartAt,
    ): void {
        $requestAt = $this->getRequestAt();
        $this->createLogBank($usrUserId, $requestAt, BankKPIF001EventId::ACTIVE->value, $usrGameStartAt);
    }

    /**
     * BANK F001 の登録イベントとしてログを記録する（BNID連携用）
     *
     * @param string $usrUserId
     * @param CarbonImmutable $now
     */
    public function createLogBankRegisteredLinkBnid(
        string $usrUserId,
        CarbonImmutable $now,
    ): void {
        $requestAt = $this->getRequestAt();
        $this->createLogBank($usrUserId, $requestAt, BankKPIF001EventId::USER_REGISTERED->value, $now);
    }

    /**
     * LogBankレコードを作成する
     *
     * @param string $usrUserId
     * @param CarbonImmutable $requestAt
     * @param string $eventId
     * @param CarbonImmutable $userFirstCreatedAt
     */
    private function createLogBank(
        string $usrUserId,
        CarbonImmutable $requestAt,
        string $eventId,
        CarbonImmutable $userFirstCreatedAt,
    ): void {
        $this->logBankRepository->create(
            $usrUserId,
            $eventId,
            $this->getPlatformUserId(),
            $userFirstCreatedAt,
            request()->header(System::HEADER_USER_AGENT, ''),
            (int) request()->header(System::HEADER_PLATFORM, '0'),
            request()->header('X-OS-Version', ''),
            request()->header('X-Country-Code', ''),
            request()->header('X-Ad-ID', ''),
            $requestAt,
        );
    }

    private function getRequestAt(): CarbonImmutable
    {
        return CarbonImmutable::createFromTimestamp(
            request()->server('REQUEST_TIME')
        );
    }

    private function getPlatformUserId(): string
    {
        $userAgent = request()->header(System::HEADER_USER_AGENT, '');

        // クライアント側のUserAgentUtilityを参考にすると、
        // PlatformUserIdと扱えるBundleIdentifierはuserAgentに入っているので、それを抽出する
        if (preg_match('/^([0-9A-Fa-f\-]+) \(/', $userAgent, $matches)) {
            return $matches[1];
        }

        // 取得できなければ'0'を返す
        return '0';
    }
}
