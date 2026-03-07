<?php

declare(strict_types=1);

namespace App\Domain\Mission\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\MissionManager;
use App\Domain\Mission\Utils\MissionUtil;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityInterface;
use App\Domain\Resource\Mst\Repositories\MstMissionAchievementRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionBeginnerRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MissionNormalClearOnCallService
{
    public function __construct(
        protected MissionManager $missionManager,
        private MstMissionAchievementRepository $mstMissionAchievementRepository,
        private MstMissionBeginnerRepository $mstMissionBeginnerRepository,
        private MissionUpdateService $missionUpdateService,
        private MissionFetchService $missionFetchService,
    ) {
    }

    /**
     * @return Collection<\App\Http\Responses\Data\UsrMissionStatusData>
     */
    public function clearOnCall(
        string $usrUserId,
        CarbonImmutable $now,
        MissionType $missionType,
        string $mstMissionId,
    ): Collection {
        $mstMission = $this->getMstMission($missionType, $mstMissionId);

        $this->validateCriterionType($mstMission->getCriterionType());

        // 複合ミッションの進捗変更の可能性があるので、トリガーして進捗更新処理を実行
        $this->missionManager->addTrigger(
            new MissionTrigger(
                $mstMission->getCriterionType(),
                $mstMission->getCriterionValue(),
                1,
            ),
            $missionType,
        );
        $this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);

        $fetchStatusData = $this->missionFetchService->fetchChangedStatusesByMissionType(
            $now,
            $missionType,
        );

        return $fetchStatusData->getUsrMissionStatusDataList();
    }

    private function validateCriterionType(string $criterionType): void
    {
        $criterionTypeEnum = MissionUtil::getCriterionTypeEnum($criterionType);

        switch ($criterionTypeEnum) {
            case MissionCriterionType::REVIEW_COMPLETED:
            case MissionCriterionType::FOLLOW_COMPLETED:
            case MissionCriterionType::ACCESS_WEB:
                // ここに到達したタイプのみクリア可能。その他はこのAPIではクリア不可。
                return;
            default:
                throw new GameException(
                    ErrorCode::MISSION_CANNOT_CLEAR,
                    'This mission cannot be cleared. criterion_type: ' . $criterionType,
                );
        }
    }

    private function getMstMission(MissionType $missionType, string $mstMissionId): ?MstMissionEntityInterface
    {
        switch ($missionType) {
            case MissionType::ACHIEVEMENT:
                return $this->mstMissionAchievementRepository->getById($mstMissionId, true);
            case MissionType::BEGINNER:
                return $this->mstMissionBeginnerRepository->getById($mstMissionId, true);
            default:
                throw new GameException(
                    ErrorCode::INVALID_PARAMETER,
                    sprintf('Mission type is invalid. mission_type: %s', $missionType->value),
                );
        }
    }
}
