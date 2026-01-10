<?php

declare(strict_types=1);

namespace App\Domain\Common\Services;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\ContentMaintenanceCheckResult;
use App\Domain\Common\Enums\ContentMaintenanceType;
use App\Domain\Resource\Mst\Repositories\MngContentCloseRepository;
use App\Domain\Resource\Mst\Repositories\MstQuestRepository;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * メンテナンス判定の共通サービス
 */
readonly class ContentMaintenanceService
{
    public function __construct(
        private UsrStageSessionRepository $usrStageSessionRepository,
        private MstStageRepository $mstStageRepository,
        private MstQuestRepository $mstQuestRepository,
        private MngContentCloseRepository $mngContentCloseRepository,
        private Clock $clock,
        private ContentMaintenanceTypeMapper $contentTypeMapper,
    ) {
    }

    private const STAGE_START_API_PATH = 'api/stage/start';

    /**
     * メンテナンス状態を判定する
     *
     * @param Request $request
     * @return ContentMaintenanceCheckResult
     */
    public function checkMaintenanceStatus(Request $request): ContentMaintenanceCheckResult
    {
        $now = $this->clock->now();

        // リクエストパスからコンテンツタイプを自動判定
        $contentType = $this->contentTypeMapper->getContentTypeFromPath($request->path());

        // コンテンツタイプが取得できない場合
        if ($contentType === null) {
            return new ContentMaintenanceCheckResult(
                contentType: null,
                isEnhanceQuest: false,
                isUnderContentMaintenance: false,
                isUnderContentMaintenanceByContentId: false,
                contentId: null
            );
        }

        // EnhanceQuest判定
        $isEnhanceQuest = false;
        if ($contentType->isEnhanceQuestType()) {
            if ($request->path() === self::STAGE_START_API_PATH) {
                // STAGE_START_API_PATHの場合はセッションが無いためパラメータから判定
                $contentId = $this->getContentIdFromRequest($request, $contentType);
                if ($contentId !== null) {
                    $isEnhanceQuest = $this->isEnhanceQuestByMstStageId($contentId);
                }
            } else {
                // それ以外のAPIの場合はユーザーのセッションから判定
                $isEnhanceQuest = $this->isEnhanceQuest($request->user()->getId());
            }
        }

        // 全体メンテナンス判定
        $isUnderContentMaintenance = $this->isUnderContentMaintenance($contentType->value, $now);

        // 個別コンテンツID取得
        $contentId = $this->getContentIdFromRequest($request, $contentType);

        // 個別メンテナンス判定
        $isUnderContentMaintenanceByContentId = false;
        if ($contentId !== null) {
            $isUnderContentMaintenanceByContentId = $this->isUnderContentMaintenanceByContentId(
                $contentType->value,
                $contentId,
                $now
            );
        }

        return new ContentMaintenanceCheckResult(
            contentType: $contentType,
            isEnhanceQuest: $isEnhanceQuest,
            isUnderContentMaintenance: $isUnderContentMaintenance,
            isUnderContentMaintenanceByContentId: $isUnderContentMaintenanceByContentId,
            contentId: $contentId
        );
    }

    /**
     * リクエストからコンテンツタイプに対応するIDを取得
     *
     * @param Request $request
     * @param ContentMaintenanceType $contentType
     * @return string
     */
    private function getContentIdFromRequest(Request $request, ContentMaintenanceType $contentType): ?string
    {
        $paramName = $contentType->getRequestParameterName();

        if ($paramName === null) {
            return null;
        }

        return $request->input($paramName);
    }

    /**
     * ユーザーのセッションがEnhanceQuestクエストかどうかを判定
     *
     * @param string $usrUserId
     * @return bool
     */
    private function isEnhanceQuest(string $usrUserId): bool
    {
        $usrStageSession = $this->usrStageSessionRepository->findByUsrUserId($usrUserId);
        if ($usrStageSession === null) {
            return false;
        }

        $mstStageId = $usrStageSession->getMstStageId();
        return $this->isEnhanceQuestByMstStageId($mstStageId);
    }

    private function isEnhanceQuestByMstStageId(string $mstStageId): bool
    {
        $mstStage = $this->mstStageRepository->getById($mstStageId);
        if ($mstStage === null) {
            return false;
        }

        $mstQuest = $this->mstQuestRepository->getById($mstStage->getMstQuestId());
        if ($mstQuest === null) {
            return false;
        }

        return $mstQuest->getQuestType() === QuestType::ENHANCE->value;
    }

    /**
     * 指定されたコンテンツタイプがメンテナンス中かどうかを判定
     *
     * @param string $contentType
     * @param CarbonImmutable $now
     * @return bool
     */
    private function isUnderContentMaintenance(string $contentType, CarbonImmutable $now): bool
    {
        $activeContentCloses = $this->mngContentCloseRepository
            ->findCurrentActiveListByContentType($contentType, $now);

        return $activeContentCloses->isNotEmpty();
    }

    /**
     * 指定されたコンテンツタイプ・IDがメンテナンス中かどうかを判定
     *
     * @param string $contentType
     * @param string $contentId
     * @param CarbonImmutable $now
     * @return bool
     */
    private function isUnderContentMaintenanceByContentId(
        string $contentType,
        string $contentId,
        CarbonImmutable $now
    ): bool {
        $activeContentCloses = $this->mngContentCloseRepository
            ->findCurrentActiveListByContentTypeAndId($contentType, $contentId, $now);

        return $activeContentCloses->isNotEmpty();
    }
}
