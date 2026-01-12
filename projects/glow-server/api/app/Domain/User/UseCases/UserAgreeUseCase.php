<?php

declare(strict_types=1);

namespace App\Domain\User\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Services\LegalDocumentService;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\User\Repositories\UsrUserRepository;

class UserAgreeUseCase
{
    use UseCaseTrait;

    public function __construct(
        private UsrUserRepository $usrUserRepository,
        private LegalDocumentService $legalDocumentService,
    ) {
    }

    /**
     * ユーザーの同意バージョン情報を更新する
     *
     * @param int $tosVersion 利用規約バージョン
     * @param int $privacyPolicyVersion プライバシーポリシーバージョン
     * @param int $globalCnsntVersion グローバルコンセントバージョン
     * @param string $language 言語設定
     * @return void
     * @throws GameException バージョン値が不正な場合
     */
    public function exec(
        CurrentUser $user,
        int $tosVersion,
        int $privacyPolicyVersion,
        int $globalCnsntVersion,
        int $iaaVersion,
        string $language,
    ): void {
        // バージョン値の検証
        $latestTosInfo = $this->legalDocumentService->getTosInfo($language);
        $latestPrivacyPolicyInfo = $this->legalDocumentService->getPrivacyPolicyInfo($language);
        $latestGlobalCnsntInfo = $this->legalDocumentService->getGlobalConsentInfo($language);
        $latestIaaInfo = $this->legalDocumentService->getIaaInfo($language);

        if (
            $tosVersion !== $latestTosInfo['version'] ||
            $privacyPolicyVersion !== $latestPrivacyPolicyInfo['version'] ||
            $globalCnsntVersion !== $latestGlobalCnsntInfo['version'] ||
            $iaaVersion !== $latestIaaInfo['version']
        ) {
            throw new GameException(ErrorCode::VALIDATION_ERROR);
        }

        $usrUser = $this->usrUserRepository->findById($user->id);
        $usrUser->setTosVersion($tosVersion);
        $usrUser->setPrivacyPolicyVersion($privacyPolicyVersion);
        $usrUser->setGlobalConsentVersion($globalCnsntVersion);
        $usrUser->setIaaVersion($iaaVersion);
        $this->usrUserRepository->syncModel($usrUser);

        // トランザクション処理
        $this->applyUserTransactionChanges();
    }
}
