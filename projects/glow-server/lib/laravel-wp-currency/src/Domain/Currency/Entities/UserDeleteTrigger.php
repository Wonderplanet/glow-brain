<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

/**
 * ログの記録をするためのデータをまとめるクラス
 * ユーザーの論理削除をする場合に使用する
 *
 * 論理削除に関連した情報はある程度固定したいため、こちらのTriggerを使用する
 */
class UserDeleteTrigger extends Trigger
{
    public function __construct(
        string $userId,
        string $triggerId,
        string $triggerDetail
    ) {
        $triggerType = Trigger::TRIGGER_TYPE_DELETE_USER;
        $detail = "soft delete user_id: {$userId}";
        if ($triggerDetail !== '') {
            $detail .= ", {$triggerDetail}";
        }
        parent::__construct($triggerType, $triggerId, '', $detail);
    }
}
