<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

/**
 * 無償一次通貨のログ記録用クラス
 *
 * @property string $id
 * @property int $logging_no
 * @property string $usr_user_id
 * @property string $os_platform
 * @property int $before_ingame_amount
 * @property int $before_bonus_amount
 * @property int $before_reward_amount
 * @property int $change_ingame_amount
 * @property int $change_bonus_amount
 * @property int $change_reward_amount
 * @property int $current_ingame_amount
 * @property int $current_bonus_amount
 * @property int $current_reward_amount
 * @property string $trigger_type
 * @property string $trigger_id
 * @property string $trigger_name
 * @property string $trigger_detail
 * @property string $request_id_type
 * @property string $request_id
 * @property string $nginx_request_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 */
class LogCurrencyFree extends BaseLogModel
{
    /**
     * マイクロ秒からシーケンス番号を生成する
     *
     * ログの行数に相当するものになるので、全体のmaxを取っていくと件数が相当増えてしまうことと、
     * クエリがひとつ増えてしまうので、この方法を考えた。
     * created_atでの重複を避けたいためのものなので、現在時刻から重複しづらい値を作成すれば目的は達成できる
     *
     * @return integer
     */
    public static function createLoggingNo(): int
    {
        return (int) (microtime(true) * 1000000);
    }
}
