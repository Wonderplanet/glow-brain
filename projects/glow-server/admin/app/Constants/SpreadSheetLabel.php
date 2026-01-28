<?php

namespace App\Constants;

// TODO: 命名変更
class SpreadSheetLabel
{
    // マスターデータがスプレッドシートにない、中間テーブル
    // const INTERMEDIATE_TABLES = ["EnemyPopArea", "FieldBreakablePopArea"];

    // const ENEMY_SHEET = 'Enemy';
    // const ENEMY_PK_COLUMN = 'enemyId';
    // const ENEMY_POP_AREA_COLUMN = 'popArea';
    // const ENEMY_POP_AREA_COLUMNS = ["id", "areaId", "enemyId", "resource"];

    // const FIELD_BREAKABLE_SHEET = 'FieldBreakable';
    // const FIELD_BREAKABLE_PK_COLUMN = 'fieldBreakableId';
    // const FIELD_BREAKABLE_POP_AREA_COLUMN = 'popArea';
    // const FIELD_BREAKABLE_POP_AREA_COLUMNS = ["id", "areaId", "fieldBreakableId", "resource"];

    const RELEASE_KEY_SHEET_NAME = 'MstReleaseKey';

    const RELEASE_KEY_COLUMN = 'releaseKey';
    const START_AT_COLUMN = 'startAt';
    const DESCRIPTION_COLUMN = 'description';

    const DATETIME_FORMAT_SPREADSHEET = 'Y-m-d H:i:sP';
    const DATETIME_FORMAT_DATABASE = 'Y-m-d H:i:s';
    const DATETIME_FORMAT_JSON = 'Y-m-d\TH:i:s.u\Z';

    const COL_NAME_IDENTIFIER = "ENABLE";
    const CATEGORY_IDENTIFIER = "CATEGORY";
    const ENABLE_ROW_IDENTIFIER = "e";
    const CATEGORY_COLUMN_ROW_IDENTIFIER = "$";

    const NULL_CELL_PLACEHOLDER = '__NULL__';

    const DATETIME_INPUT_TIMEZONE = 'Asia/Tokyo';
}
