<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\CurrencyTriggers;

use WonderPlanet\Domain\Currency\Entities\Trigger;

/**
 * 課金・通貨ライブラリのトリガー
 *
 * 追加または消費する場合の理由を記録するためのクラス
 * プロダクト向けに変更する場合があるため、Triggerクラスを継承して使用する
 */
abstract class CurrencyBaseTrigger extends Trigger
{
    /**
     * トリガーのTYPEを設定する
     *
     * 継承先のクラスでここをオーバーライドする
     */
    public const TYPE = 'unknonw';

    /**
     * トリガーのTYPEを取得する
     *
     * @return string
     */
    public function getTriggerType(): string
    {
        return static::TYPE;
    }

    /**
     * コンストラクタ
     *
     * trigger_typeはクラスで固定しているので、それを使用する
     *
     * triggerDetailsはJSON形式で保存するので、配列を渡す
     *
     * @param string $triggerId
     * @param string $triggerName
     * @param array<string, mixed> $triggerDetails
     */
    public function __construct(
        string $triggerId,
        string $triggerName,
        array $triggerDetails = []
    ) {
        $triggerType = $this->getTriggerType();

        // detailはjson形式に変換する
        $triggerDetail = json_encode($triggerDetails, JSON_UNESCAPED_UNICODE);

        parent::__construct($triggerType, $triggerId, $triggerName, $triggerDetail);
    }
}
