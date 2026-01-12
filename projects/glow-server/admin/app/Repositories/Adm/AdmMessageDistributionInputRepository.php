<?php

namespace App\Repositories\Adm;

use App\Models\Adm\AdmMessageDistributionInput;
use App\Models\Adm\Base\BaseAdmMessageDistributionInput;
use App\Models\Adm\Enums\AdmMessageCreateStatuses;
use App\Repositories\Adm\Base\AdmMessageDistributionInputRepositoryInterface;
use Carbon\CarbonImmutable;

class AdmMessageDistributionInputRepository implements AdmMessageDistributionInputRepositoryInterface
{
    /**
     * @inheritdoc
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
    ): BaseAdmMessageDistributionInput {
        $admMessageDistributionInput = new AdmMessageDistributionInput();
        $admMessageDistributionInput->create_status = $createStatus;
        $admMessageDistributionInput->title = $title;
        $admMessageDistributionInput->start_at = $startAt;
        $admMessageDistributionInput->expired_at = $expiredAt;
        $admMessageDistributionInput->mng_message_id = $mngMessageId;
        $admMessageDistributionInput->mng_messages_txt = $mngMessagesTxt;
        $admMessageDistributionInput->mng_message_distributions_txt = $mngMessageDistributionsTxt;
        $admMessageDistributionInput->mng_message_i18ns_txt = $mngMessageI18nsTxt;
        $admMessageDistributionInput->target_type = $targetType;
        $admMessageDistributionInput->target_ids_txt = $targetIdsTxt;
        $admMessageDistributionInput->display_target_id_input_type = $displayTargetIdInputType;
        $admMessageDistributionInput->account_created_type = $accountCreatedType;
        $admMessageDistributionInput->adm_promotion_tag_id = $admPromotionTagId;
        $admMessageDistributionInput->save();

        return $admMessageDistributionInput;
    }

    /**
     * @inheritdoc
     */
    public function update(array $update, int $id): int
    {
        return AdmMessageDistributionInput::query()
            ->where('id', $id)
            ->update($update);
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id): int
    {
        return AdmMessageDistributionInput::query()
            ->where('id', $id)
            ->delete();
    }

    /**
     * @inheritdoc
     */
    public function getById(int $id): ?BaseAdmMessageDistributionInput
    {
        return AdmMessageDistributionInput::query()
            ->find($id);
    }
}
