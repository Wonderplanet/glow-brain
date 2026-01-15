<?php

namespace App\Repositories\Adm;

use App\Models\Adm\AdmMessageDistributionIndividualInput;
use App\Models\Adm\Base\BaseAdmMessageDistributionInput;
use App\Models\Adm\Enums\AdmMessageCreateStatuses;
use App\Repositories\Adm\Base\AdmMessageDistributionInputRepositoryInterface;
use Carbon\CarbonImmutable;

class AdmMessageDistributionIndividualInputRepository implements AdmMessageDistributionInputRepositoryInterface
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
        $admMessageDistributionIndividualInput = new AdmMessageDistributionIndividualInput();
        $admMessageDistributionIndividualInput->create_status = $createStatus;
        $admMessageDistributionIndividualInput->title = $title;
        $admMessageDistributionIndividualInput->start_at = $startAt;
        $admMessageDistributionIndividualInput->expired_at = $expiredAt;
        $admMessageDistributionIndividualInput->mng_message_id = $mngMessageId;
        $admMessageDistributionIndividualInput->mng_messages_txt = $mngMessagesTxt;
        $admMessageDistributionIndividualInput->mng_message_distributions_txt = $mngMessageDistributionsTxt;
        $admMessageDistributionIndividualInput->mng_message_i18ns_txt = $mngMessageI18nsTxt;
        $admMessageDistributionIndividualInput->target_type = $targetType;
        $admMessageDistributionIndividualInput->target_ids_txt = $targetIdsTxt;
        $admMessageDistributionIndividualInput->display_target_id_input_type = $displayTargetIdInputType;
        $admMessageDistributionIndividualInput->account_created_type = $accountCreatedType;
        $admMessageDistributionIndividualInput->adm_promotion_tag_id = $admPromotionTagId;
        $admMessageDistributionIndividualInput->save();

        return $admMessageDistributionIndividualInput;
    }

    /**
     * @inheritdoc
     */
    public function update(array $update, int $id): int
    {
        return AdmMessageDistributionIndividualInput::query()
            ->where('id', $id)
            ->update($update);
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id): int
    {
        return AdmMessageDistributionIndividualInput::query()
            ->where('id', $id)
            ->delete();
    }

    /**
     * @inheritdoc
     */
    public function getById(int $id): ?BaseAdmMessageDistributionInput
    {
        return AdmMessageDistributionIndividualInput::query()
            ->find($id);
    }
}
