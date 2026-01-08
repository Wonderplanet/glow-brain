<?php

declare(strict_types=1);

namespace App\Domain\Message\Models;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Message\Models\UsrMessageInterface;
use App\Domain\Resource\Usr\Models\UsrModel;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrMessage extends UsrModel implements UsrMessageInterface
{
    protected static string $tableName = 'usr_messages';
    protected array $modelKeyColumns = ['id'];

    public static function create(
        string $usrUserId,
        ?string $mngMessageId,
        ?string $messageSource = null,
        ?string $rewardGroupId = null,
        ?CarbonImmutable $expiredAt = null,
        ?string $resourceType = null,
        ?string $resourceId = null,
        ?int $resourceAmount = null,
        ?string $title = null,
        ?string $body = null,
    ): UsrMessageInterface {
        return new self([
            'usr_user_id' => $usrUserId,
            'mng_message_id' => $mngMessageId,
            'message_source' => $messageSource,
            'reward_group_id' => $rewardGroupId,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'resource_amount' => $resourceAmount,
            'is_received' => false,
            'title' => $title,
            'body' => $body,
            'opened_at' => null,
            'received_at' => null,
            'expired_at' => $expiredAt?->toDateTimeString(),
        ]);
    }

    public function getMngMessageId(): ?string
    {
        return $this->attributes['mng_message_id'];
    }

    public function setMngMessageId(?string $mngMessageId): void
    {
        $this->attributes['mng_message_id'] = $mngMessageId;
    }

    public function getMessageSource(): ?string
    {
        return $this->attributes['message_source'];
    }

    public function setMessageSource(?string $messageSource): void
    {
        $this->attributes['message_source'] = $messageSource;
    }

    /**
     * 経緯情報の要素を連結した文字列を作る
     * @param Collection<string> $messageSourceParts メッセージを受け取った経緯情報を要素にもつ配列
     * @return string
     */
    public static function makeCombinedMessageSource(Collection $messageSourceParts): string
    {
        return $messageSourceParts->filter(function ($value) {
            return StringUtil::isSpecified($value);
        })->implode(':');
    }

    public function getRewardGroupId(): ?string
    {
        return $this->attributes['reward_group_id'];
    }

    public function setRewardGroupId(?string $rewardGroupId): void
    {
        $this->attributes['reward_group_id'] = $rewardGroupId;
    }

    public function getResourceType(): ?string
    {
        return $this->attributes['resource_type'];
    }

    public function setResourceType(?string $resourceType): void
    {
        $this->attributes['resource_type'] = $resourceType;
    }

    public function getResourceId(): ?string
    {
        return $this->attributes['resource_id'];
    }

    public function setResourceId(?string $resourceId): void
    {
        $this->attributes['resource_id'] = $resourceId;
    }

    public function getResourceAmount(): ?int
    {
        return $this->attributes['resource_amount'];
    }

    public function setResourceAmount(?int $resourceAmount): void
    {
        $this->attributes['resource_amount'] = $resourceAmount;
    }

    public function getTitle(): ?string
    {
        return $this->attributes['title'];
    }

    public function setTitle(?string $title): void
    {
        $this->attributes['title'] = $title;
    }

    public function getBody(): ?string
    {
        return $this->attributes['body'];
    }

    public function setBody(?string $body): void
    {
        $this->attributes['body'] = $body;
    }

    public function getOpenedAt(): ?string
    {
        return $this->attributes['opened_at'];
    }

    public function setOpenedAt(?CarbonImmutable $openedAt): void
    {
        $this->attributes['opened_at'] = $openedAt?->toDateTimeString();
    }

    public function getReceivedAt(): ?string
    {
        return $this->attributes['received_at'];
    }

    public function setReceivedAt(?CarbonImmutable $receivedAt): void
    {
        $this->attributes['received_at'] = $receivedAt?->toDateTimeString();
    }

    public function getIsReceived(): bool
    {
        return (bool) $this->attributes['is_received'];
    }

    public function setIsReceived(bool $isReceived): void
    {
        $this->attributes['is_received'] = (int) $isReceived;
    }

    public function getExpiredAt(): ?string
    {
        return $this->attributes['expired_at'];
    }

    public function setExpiredAt(?CarbonImmutable $expiredAt): void
    {
        $this->attributes['expired_at'] = $expiredAt?->toDateTimeString();
    }

    public function getCreatedAt(): ?string
    {
        if (!array_key_exists('created_at', $this->attributes)) {
            return null;
        }
        return $this->attributes['created_at'];
    }

    /**
     * 期限切れかチェック
     *
     * @param CarbonImmutable $now
     * @return bool true:期限切れ、false:期間内
     */
    public function isExpired(CarbonImmutable $now): bool
    {
        if (is_null($this->getExpiredAt())) {
            // 期限がnullだった場合は常にfalse
            return false;
        }
        // 比較して期限内か判別
        return CarbonImmutable::parse($this->getExpiredAt()) < $now;
    }

    /**
     * 報酬の受け取り情報を格納
     *
     * @param CarbonImmutable $receivedAt
     * @return void
     */
    public function receive(CarbonImmutable $receivedAt): void
    {
        $this->setReceivedAt($receivedAt);
        $this->setIsReceived(true);
    }
}
