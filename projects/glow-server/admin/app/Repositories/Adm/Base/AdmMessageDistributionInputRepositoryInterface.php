<?php

namespace App\Repositories\Adm\Base;

use App\Models\Adm\Base\BaseAdmMessageDistributionInput;
use App\Models\Adm\Enums\AdmMessageCreateStatuses;
use Carbon\CarbonImmutable;

interface AdmMessageDistributionInputRepositoryInterface
{
    /**
     * @param AdmMessageCreateStatuses $createStatus
     * @param string $title
     * @param CarbonImmutable $startAt
     * @param CarbonImmutable|null $expiredAt
     * @param string|null $mngMessageId
     * @param string|null $mngMessagesTxt
     * @param string|null $mngMessageDistributionsTxt
     * @param string|null $mngMessageI18nsTxt
     * @param string $targetType
     * @param string|null $targetIdsTxt
     * @param string $displayTargetIdInputType
     * @param string $accountCreatedType
     * @param string|null $admPromotionTagId
     * @return BaseAdmMessageDistributionInput
     */
    public function create(
        AdmMessageCreateStatuses $createStatus,
        string $title,
        CarbonImmutable $startAt,
        CarbonImmutable|null $expiredAt,
        ?string $mngMessageId,
        ?string $mngMessagesTxt,
        ?string $mngMessageDistributionsTxt,
        ?string $mngMessageI18nsTxt,
        string $targetType,
        ?string $targetIdsTxt,
        string $displayTargetIdInputType,
        string $accountCreatedType,
        ?string $admPromotionTagId
    ): BaseAdmMessageDistributionInput;

    /**
     * 対象idのデータを更新
     *
     * @param array $update
     * @param int $id
     * @return int
     */
    public function update(array $update, int $id): int;

    /**
     * 対象idのデータを削除
     *
     * @param int $id
     * @return int
     */
    public function delete(int $id): int;

    /**
     * @param int $id
     * @return BaseAdmMessageDistributionInput|null
     */
    public function getById(int $id): ?BaseAdmMessageDistributionInput;
}
