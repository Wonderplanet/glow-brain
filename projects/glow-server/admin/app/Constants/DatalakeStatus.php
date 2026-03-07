<?php

declare(strict_types=1);

namespace App\Constants;

enum DatalakeStatus: int
{
    case NOT_STARTED = 0; // 未実行
    case MST_DB_TRANSFERRED = 1; // mstDB転送完了
    case OPR_DB_TRANSFERRED = 2; // oprDB転送完了
    case USR_DB_TRANSFERRED = 3; // usrDB転送完了
    case LOG_DB_TRANSFERRED = 4; // logDB転送完了
    case COMPLETED = 5; // 完了

    public function label(): string
    {
        return match ($this) {
            self::NOT_STARTED => '未実行',
            self::MST_DB_TRANSFERRED => 'mstDB転送完了',
            self::OPR_DB_TRANSFERRED => 'oprDB転送完了',
            self::USR_DB_TRANSFERRED => 'usrDB転送完了',
            self::LOG_DB_TRANSFERRED => 'logDB転送完了',
            self::COMPLETED => '完了',
        };
    }

    public function getNextStatus(): ?self
    {
        return match ($this) {
            self::NOT_STARTED => self::MST_DB_TRANSFERRED,
            self::MST_DB_TRANSFERRED => self::OPR_DB_TRANSFERRED,
            self::OPR_DB_TRANSFERRED => self::USR_DB_TRANSFERRED,
            self::USR_DB_TRANSFERRED => self::LOG_DB_TRANSFERRED,
            // LOG_DB_TRANSFERREDの場合も次のステータスはCOMPLETED
            default => self::COMPLETED,
        };
    }
}
