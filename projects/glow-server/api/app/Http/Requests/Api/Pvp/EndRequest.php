<?php

namespace App\Http\Requests\Api\Pvp;

use App\Http\Lib\Requests\BaseApiRequest;
use App\Http\Requests\Traits\PartyStatusRequestTrait;

/**
 * @method string getSysPvpSeasonId()
 * @method array getInGameBattleLog()
 * @method bool getIsWin()
 */
class EndRequest extends BaseApiRequest
{
    use PartyStatusRequestTrait;

    /**
     * リクエストパラメータをキャストする型を記載する
     * getterからキー名を取得するため、getterやプロパティ名と同じ名前で記載する
     *
     * @var array<string, string>
     */
    protected static $casts = [
        'sysPvpSeasonId' => 'string',
        'isWin' => 'bool',
    ];

    /**
     * 取得ルールは実際に送信されてくるキーで記載する
     * キャメルケースの場合はキャメルケースで、スネークケースの場合はスネークケースで記載する
     *
     * @var array<string, string>
     */
    protected static $rules = [
        'sysPvpSeasonId' => 'required',
        'isWin' => 'required|boolean',
        'inGameBattleLog' => 'required|array',
        'inGameBattleLog.clearTimeMs' => 'required',
        'inGameBattleLog.maxDamage' => 'required',
    ];

    /**
     * ここに記載されるプロパティは、リクエストのキーがスネークケースで送られてくる
     * getterなどはキャメルケースで扱うので、取得する際に内部で変換される
     *
     * @var array<string>
     */
    protected static $castSnakeCase = [
    ];

    /**
     * @var string
     */
    protected string $sysPvpSeasonId;

    /**
     * @var bool
     */
    protected bool $isWin;
    
    /**
     * @var array<mixed>
     */
    protected array $inGameBattleLog;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return array_merge(
            static::$rules,
            $this->getPartyStatusRules('inGameBattleLog'),
        );
    }
}
