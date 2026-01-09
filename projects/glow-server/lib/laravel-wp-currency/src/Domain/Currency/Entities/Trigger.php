<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

/**
 * ログの記録をするためのデータをまとめるクラス
 */
class Trigger
{
    // 課金・通貨基盤で使用するトリガー
    /**
     * 課金・通過基盤プラットフォームの初期化処理時に追加
     */
    public const TRIGGER_TYPE_PF_INIT = 'pf_init';

    /**
     * allowanceの登録時に追加
     */
    public const TRIGGER_TYPE_ALLOWANCE_INSERT = 'insert';

    /**
     * allowanceの削除時に追加
     */
    public const TRIGGER_TYPE_ALLOWANCE_DELETE = 'delete';

    /**
     * ユーザーを論理削除する際に追加
     */
    public const TRIGGER_TYPE_DELETE_USER = 'delete_user';

    /**
     * 一次通貨の返却時に追加
     */
    public const TRIGGER_TYPE_REVERT_CURRENCY = 'revert_currency';

    /**
     * 管理ツールから無償一次通貨の回収時に追加
     */
    public const TRIGGER_TYPE_COLLECT_CURRENCY_FREE_ADMIN = 'collect_currency_free';

    /**
     * バッチから無償一次通貨の回収時に追加
     */
    public const TRIGGER_TYPE_COLLECT_CURRENCY_FREE_BATCH = 'collect_currency_free_batch';

    /**
     * 管理ツールから有償一次通貨の回収時に追加
     */
    public const TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN = 'collect_currency_paid';

    /**
     * バッチから有償一次通貨の回収時に追加
     */
    public const TRIGGER_TYPE_COLLECT_CURRENCY_PAID_BATCH = 'collect_currency_paid_batch';

    /**
     * 無償一次通貨の付与時に追加
     */
    public const TRIGGER_TYPE_ADD_CURRENCY_FREE_BATCH = 'add_currency_free_batch';

    /**
     * ロギングの発生契機
     *
     * @var string
     */
    public string $triggerType;

    /**
     * 関連するID (ガチャならガチャID、課金ならproduct_sub_idなど)
     *
     * @var string
     */
    public string $triggerId;

    /**
     * 関連する名前 (ガチャならガチャ名、課金なら商品名など)
     *
     * @var string
     */
    public string $triggerName;

    /**
     * その他の情報
     *
     * @var string
     */
    public string $triggerDetail;

    public function __construct(
        string $triggerType,
        string $triggerId,
        string $triggerName,
        string $triggerDetail
    ) {
        $this->triggerType = $triggerType;
        $this->triggerId = $triggerId;
        $this->triggerName = $triggerName;
        $this->triggerDetail = $triggerDetail;
    }
}
