<?php

declare(strict_types=1);

namespace App\Domain\Stage\Factories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Services\StageEndEnhanceQuestService;
use App\Domain\Stage\Services\StageEndEventQuestService;
use App\Domain\Stage\Services\StageEndNormalQuestService;
use App\Domain\Stage\Services\StageEndQuestService;
use App\Domain\Stage\Services\StageEndSpeedAtttackQuestService;
use App\Domain\Stage\Services\StageService;
use App\Domain\Stage\Services\StageStartEnhanceQuestService;
use App\Domain\Stage\Services\StageStartEventQuestService;
use App\Domain\Stage\Services\StageStartNormalQuestService;
use App\Domain\Stage\Services\StageStartQuestService;
use Carbon\CarbonImmutable;

class QuestServiceFactory
{
    public function __construct(
        // Service
        private StageService $stageService,
    ) {
    }

    public function getStageStartQuestService(string $questType): StageStartQuestService
    {
        return match ($questType) {
            QuestType::NORMAL->value => app()->make(StageStartNormalQuestService::class),
            QuestType::EVENT->value => app()->make(StageStartEventQuestService::class),
            QuestType::ENHANCE->value => app()->make(StageStartEnhanceQuestService::class),
            default => throw new GameException(
                ErrorCode::QUEST_TYPE_NOT_FOUND,
                sprintf('QuestType not found. (quest_type: %s)', $questType),
            ),
        };
    }

    public function getStageEndQuestService(
        string $questType,
        string $mstStageId,
        CarbonImmutable $now,
    ): StageEndQuestService {
        return match ($questType) {
            QuestType::NORMAL->value => app()->make(StageEndNormalQuestService::class),
            QuestType::EVENT->value => $this->getStageEndEventQuestService($mstStageId, $now),
            QuestType::ENHANCE->value => app()->make(StageEndEnhanceQuestService::class),
            default => throw new GameException(
                ErrorCode::QUEST_TYPE_NOT_FOUND,
                sprintf('QuestType not found. (quest_type: %s)', $questType),
            ),
        };
    }

    private function getStageEndEventQuestService(string $mstStageId, CarbonImmutable $now): StageEndQuestService
    {
        $isSpeedAttack = $this->stageService->isSpeedAttack($mstStageId, $now);

        if ($isSpeedAttack) {
            return app()->make(StageEndSpeedAtttackQuestService::class);
        }

        return app()->make(StageEndEventQuestService::class);
    }
}
