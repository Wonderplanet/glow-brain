<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\InGame\Enums\InGameSpecialRuleType;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Mst\Entities\MstInGameSpecialRuleEntity as Entity;
use App\Domain\Resource\Mst\Models\MstInGameSpecialRule as Model;
use App\Domain\Resource\Mst\Traits\MstRepositoryTrait;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstInGameSpecialRuleRepository
{
    use MstRepositoryTrait;

    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    public function getByContentTypeAndTargetId(
        InGameContentType $contentType,
        string $targetId,
        CarbonImmutable $now
    ): Collection {
        return $this->masterRepository->getByColumns(
            Model::class,
            [
                'content_type' => $contentType->value,
                'target_id' => $targetId,
            ],
        )->filter(
            function (Entity $entity) use ($now) {
                return $this->isActiveEntity($entity, $now);
            }
        );
    }

    public function getByContentTypeAndTargetIdAndRuleType(
        InGameContentType $contentType,
        string $targetId,
        InGameSpecialRuleType $ruleType,
        CarbonImmutable $now
    ): Collection {
        $ruleTypeStr = $ruleType->value;

        return $this->getByContentTypeAndTargetId($contentType, $targetId, $now)
            ->filter(
                function (Entity $entity) use ($ruleTypeStr) {
                    return $entity->getRuleType() === $ruleTypeStr;
                }
            );
    }

    public function getByContentTypeAndRuleType(
        InGameContentType $contentType,
        InGameSpecialRuleType $ruleType,
        CarbonImmutable $now
    ): Collection {
        $contentTypeStr = $contentType->value;

        return $this->masterRepository->getByColumn(
            Model::class,
            'rule_type',
            $ruleType->value,
        )->filter(
            function (Entity $entity) use ($contentTypeStr, $now) {
                return $entity->getContentType() === $contentTypeStr
                    && $this->isActiveEntity($entity, $now);
            }
        );
    }
}
