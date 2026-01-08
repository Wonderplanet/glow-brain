<?php

declare(strict_types=1);

namespace App\Domain\Constants;

class Database
{
    public const MST_CONNECTION = 'mst';

    public const MNG_CONNECTION = 'mng';

    public const TIDB_CONNECTION = 'tidb';

    public const ADMIN_CONNECTION = 'admin';

    // コネクションとマイグレーションファイルのディレクトリ指定の定数配列
    public const MIGRATION_FILES = [
        self::MST_CONNECTION => 'migrations/mst',
        self::MNG_CONNECTION => 'migrations/mng',
        self::TIDB_CONNECTION => 'migrations',
    ];
}
