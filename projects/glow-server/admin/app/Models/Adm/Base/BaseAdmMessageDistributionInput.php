<?php

namespace App\Models\Adm\Base;

use App\Constants\Database;
use App\Models\Adm\Enums\AdmMessageCreateStatuses;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property AdmMessageCreateStatuses $create_status
 * @property string $title
 * @property CarbonImmutable $start_at
 * @property CarbonImmutable|null $expired_at
 * @property string|null $mng_message_id
 * @property string $mng_messages_txt
 * @property string $mng_message_distributions_txt
 * @property string $mng_message_i18ns_txt
 * @property string $target_type
 * @property string|null $target_ids_txt
 * @property string $display_target_id_input_type
 * @property string $account_created_type
 * @property string $adm_promotion_tag_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
abstract class BaseAdmMessageDistributionInput extends Model
{
    use HasFactory;

    protected $connection = Database::ADMIN_CONNECTION;

    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'create_status' => AdmMessageCreateStatuses::class,
        'start_at' => 'datetime:Y-m-d H:i:s',
        'expired_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreateStatus(): AdmMessageCreateStatuses
    {
        return $this->create_status;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getStartAt(): CarbonImmutable
    {
        return $this->start_at;
    }

    public function getExpiredAt(): CarbonImmutable|null
    {
        return $this->expired_at;
    }

    public function getMngMessageId(): string|null
    {
        return $this->mng_message_id;
    }

    public function getMngMessagesTxt(): string
    {
        return $this->mng_messages_txt;
    }

    public function getMngMessageDistributionsTxt(): string
    {
        return $this->mng_message_distributions_txt;
    }

    public function getMngMessageI18nsTxt(): string
    {
        return $this->mng_message_i18ns_txt;
    }

    public function getTargetType(): string
    {
        return $this->target_type;
    }

    public function getTargetIdsTxt(): ?string
    {
        return $this->target_ids_txt;
    }

    public function getDisplayTargetIdInputType(): string
    {
        return $this->display_target_id_input_type;
    }

    public function getAccountCreatedType(): string
    {
        return $this->account_created_type;
    }

    /**
     * @return array|null
     */
    public function getUnserializedMngMessages(): ?array
    {
        return is_null($this->mng_messages_txt)
            ? null
            : unserialize($this->mng_messages_txt, ['allowed_classes' => [CarbonImmutable::class]]);
    }

    /**
     * @return Collection|null
     */
    public function getUnserializedMngMessageDistributions(): ?Collection
    {
        return is_null($this->mng_message_distributions_txt)
            ? null
            : unserialize($this->mng_message_distributions_txt, ['allowed_classes' => [Collection::class]]);
    }

    /**
     * @return Collection|null
     */
    public function getUnserializedMngMessageI18ns(): ?Collection
    {
        return is_null($this->mng_message_i18ns_txt)
            ? null
            : unserialize($this->mng_message_i18ns_txt, ['allowed_classes' => [Collection::class]]);
    }

    public function getAdmPromotionTagId(): ?string
    {
        return $this->adm_promotion_tag_id;
    }

    /**
     * 昇格時に使用する。配布済みステータスから、配布前ステータスに戻す
     * @return void
     */
    public function revertToUndistributed(): void
    {
        // 配布済みのデータを未配布に戻す
        $this->create_status = AdmMessageCreateStatuses::Pending; // 配布前
    }

    /**
     * 昇格用の比較メソッド。
     * 昇格元と昇格先のデータで、差があるかどうかを比較する。
     * 差があれば昇格を行なって変更を更新する必要がある。
     *
     * @param BaseAdmMessageDistributionInput $other
     * @return bool true: 同じ内容、false: 異なる内容
     */
    public function isSameAsForPromotion(BaseAdmMessageDistributionInput $other): bool
    {
        // メッセージ内容に関わるデータのみを対象とする
        $targetColumns = [
            'title',
            'start_at',
            'expired_at',
            'mng_message_id',
            'mng_messages_txt',
            'mng_message_distributions_txt',
            'mng_message_i18ns_txt',
            'target_type',
            'target_ids_txt',
            'display_target_id_input_type',
            'account_created_type',
        ];

        foreach ($targetColumns as $column) {
            if ($this->{$column} != $other->{$column}) {
                return false;
            }
        }

        return true;
    }

    public function alreadyDistributed(CarbonImmutable $now): bool
    {
        return $this->create_status === AdmMessageCreateStatuses::Approved
            && $this->start_at <= $now;
    }

    public function formatToResponse(): array
    {
        return parent::toArray();
    }

    public static function createFromResponseArray(array $response): static
    {
        $model = new static();
        $model->fill($response);
        return $model;
    }

    public function formatToInsertArray(): array
    {
        $array = $this->toArray();

        $now = CarbonImmutable::now();
        $array['created_at'] = $now;
        $array['updated_at'] = $now;

        return $array;
    }
}
