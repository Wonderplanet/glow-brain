<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Enums\RewardSendMethod;
use App\Domain\Resource\Enums\RewardType;

class RewardSendPolicy
{
    /**
     * @param ?GameException $resourceLimitReachedException リソース上限超過時に投げる例外(必要であれば指定する)
     *   使用例：メール受取時に上限超過したものがあれば、メール受け取れません例外を投げる
     */
    public function __construct(
        private RewardSendMethod $coinSendMethod = RewardSendMethod::NONE,
        private RewardSendMethod $freeDiamondSendMethod = RewardSendMethod::SEND_TO_MESSAGE,
        private RewardSendMethod $staminaSendMethod = RewardSendMethod::NONE,
        private RewardSendMethod $itemSendMethod = RewardSendMethod::NONE,
        private RewardSendMethod $expSendMethod = RewardSendMethod::NONE,
        private RewardSendMethod $emblemSendMethod = RewardSendMethod::NONE,
        private RewardSendMethod $unitSendMethod = RewardSendMethod::NONE,
        private ?GameException $resourceLimitReachedException = null,
    ) {
    }

    public static function createDefaultPolicy(): self
    {
        return new self();
    }

    /**
     * リソース上限超過したら指定された例外を投げるポリシー
     */
    public static function createThrowErrorWhenResourceLimitReachedPolicy(
        GameException $resourceLimitReachedException,
    ): self {
        return new self(
            coinSendMethod: RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED,
            freeDiamondSendMethod: RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED,
            staminaSendMethod: RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED,
            itemSendMethod: RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED,
            expSendMethod: RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED,
            emblemSendMethod: RewardSendMethod::NONE, // 重複したらコインに変換される仕様なので例外は投げない
            unitSendMethod: RewardSendMethod::NONE, // 重複したらキャラのかけらアイテムに変換される仕様なので例外は投げない
            resourceLimitReachedException: $resourceLimitReachedException,
        );
    }

    public function getSendMethodByRewardType(string $rewardType): RewardSendMethod
    {
        return match ($rewardType) {
            RewardType::COIN->value => $this->coinSendMethod,
            RewardType::FREE_DIAMOND->value => $this->freeDiamondSendMethod,
            RewardType::STAMINA->value => $this->staminaSendMethod,
            RewardType::ITEM->value => $this->itemSendMethod,
            RewardType::EXP->value => $this->expSendMethod,
            RewardType::EMBLEM->value => $this->emblemSendMethod,
            RewardType::UNIT->value => $this->unitSendMethod,
            default => RewardSendMethod::NONE,
        };
    }

    /**
     * exceptionが設定されている場合は例外を投げる
     * @return void
     * @throws GameException
     */
    public function throwResourceLimitReachedExceptionIfSet(): void
    {
        if ($this->resourceLimitReachedException !== null) {
            throw $this->resourceLimitReachedException;
        }
    }

    /**
     * 指定された報酬タイプのうち、上限超過時に例外を投げるものを返す
     * @param array<string> $rewardTypes $rewardTypes チェック対象の報酬タイプ RewardType enum の値を要素にもつ配列
     * @return array<string> 上限超過時に例外を投げる報酬タイプの配列
     */
    public function getRewardTypesOfThrowErrorWhenResourceLimitReached(array $rewardTypes): array
    {
        $result = [];

        $rewardTypes = array_unique($rewardTypes);

        foreach ($rewardTypes as $rewardType) {
            $sendMethod = $this->getSendMethodByRewardType($rewardType);
            if ($sendMethod === RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED) {
                $result[] = $rewardType;
            }
        }

        return $result;
    }
}
