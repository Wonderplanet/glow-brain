<?php

declare(strict_types=1);

namespace App\Domain\Cheat\Constants;

class CheatConstant
{
    /**
     * メッセージをログに出力する際のキー
     */
    public const LOG_DETAIL_KEY_MSG = 'msg';

    /**
     * バトル時間をログに出力する際のキー
     * APIリクエストから：不正確なデータ。チート疑い前提でチェックが必須
     */
    public const LOG_DETAIL_KEY_BATTLE_TIME_SECONDS = 'battleTimeSeconds';

    /**
     * 最大ダメージをログに出力する際のキー
     * APIリクエストから：不正確なデータ。チート疑い前提でチェックが必須
     */
    public const LOG_DETAIL_KEY_MAX_DAMAGE = 'maxDamage';

    /**
     * ユニット情報をログに出力する際のキー
     * usrDBから：一番正確なデータ。チェックは不要
     */
    public const LOG_DETAIL_KEY_UNIT_DATA = 'usrUnits';

    /**
     * パーティ情報をログに出力する際のキー
     * APIリクエストから：不正確なデータ。チート疑い前提でチェックが必須
     */
    public const LOG_DETAIL_KEY_PARTY_STATUSES = 'requestPartyStatuses';

    /**
     * APIリクエストから：不正確なデータ。チート疑い前提でチェックが必須
     */
    public const LOG_DETAIL_KEY_BEFORE_BATTLE_PARTY_STATUSES = 'beforeBattlePartyStatuses';
}
