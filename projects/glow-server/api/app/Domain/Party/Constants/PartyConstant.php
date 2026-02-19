<?php

declare(strict_types=1);

namespace App\Domain\Party\Constants;

class PartyConstant
{
    /** @var int パーティ数 */
    public const INITIAL_PARTY_COUNT = 10;

    /** @var int パーティ名の最大文字数 */
    public const MAX_PARTY_NAME_LENGTH = 10;

    /** @var int パーティ内の最大ユニット数 */
    public const MAX_UNIT_COUNT_IN_PARTY = 10;

    /** @var int 初期パーティ内の最大ユニット数 */
    public const MAX_UNIT_COUNT_IN_FIRST_PARTY = 5;

    /**
     * mst_party_unit_countsテーブルで設定される、初期パーティの最大ユニット数が設定されるmst_stage_id
     * @var string
     */
    public const MIN_MST_PARTY_UNIT_COUNT_MST_STAGE_ID = '';

    /** @var int パーティNoの最初の番号 */
    public const FIRST_PARTY_NO = 1;
}
