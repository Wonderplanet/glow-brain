<?php

namespace App\Http\Requests\Api\AdventBattle;

use App\Http\Lib\Requests\BaseApiRequest;

/**
 * @method int getAbortType()
 */
class AbortRequest extends BaseApiRequest
{
    /**
     * リクエストパラメータをキャストする型を記載する
     * getterからキー名を取得するため、getterやプロパティ名と同じ名前で記載する
     *
     * @var array<string, string>
     */
    protected static $casts = [
        'abortType' => 'int',
    ];

    /**
     * 取得ルールは実際に送信されてくるキーで記載する
     * キャメルケースの場合はキャメルケースで、スネークケースの場合はスネークケースで記載する
     *
     * @var array<string, string>
     */
    protected static $rules = [
        'abortType' => 'required',
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
     * @var int
     */
    protected int $abortType;
}
