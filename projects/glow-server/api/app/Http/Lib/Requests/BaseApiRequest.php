<?php

declare(strict_types=1);

namespace App\Http\Lib\Requests;

use App\Domain\Common\Constants\System;
use App\Domain\Common\Utils\PlatformUtil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use UnitEnum;

/**
 * APIリクエストの基底クラス
 */
abstract class BaseApiRequest extends FormRequest
{
    /**
     * リクエストパラメータをキャストする型を記載する
     * getterからキー名を取得するため、getterやプロパティ名と同じ名前で記載する
     *
     * @var array<string, string>
     */
    protected static $casts = [];

    /**
     * 取得ルールは実際に送信されてくるキーで記載する
     * キャメルケースの場合はキャメルケースで、スネークケースの場合はスネークケースで記載する
     *
     * @var array<string, string>
     */
    protected static $rules = [];

    /**
     * ここに記載されるプロパティは、リクエストのキーがスネークケースで送られてくる
     * getterなどはキャメルケースで扱うので、取得する際に内部で変換される
     *
     * @var array<string>
     */
    protected static $castSnakeCase = [];

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return static::$rules;
    }

    /**
     * @return array<string>
     */
    public function castSnakeCase(): array
    {
        return static::$castSnakeCase;
    }

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @param string $name
     * @param string $docType
     * @param mixed|null $default
     * @return mixed
     */
    public function castedInput(string $name, string $docType, mixed $default = null): mixed
    {
        return $this->castValue($docType, $this->inputInternal($name, $default));
    }

    /**
     * このrequestクラスの内部処理としてinputから取得する
     *
     * castSnakeCase()に記載されているパラメータに一致する場合はスネークケースで取得する
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    private function inputInternal(string $name, mixed $default = null): mixed
    {
        if (in_array($name, $this->castSnakeCase(), true)) {
            return $this->input(Str::snake($name), $default);
        }
        return $this->input($name, $default);
    }

    /**
     * @param string $docType
     * @param mixed $value
     * @return int|float|string|\Illuminate\Support\Collection|bool|null|UnitEnum
     */
    protected function castValue(string $docType, mixed $value): mixed
    {
        // 配列であれば中身をキャストして返すことになるので、配列かどうかの分岐は最初に行う
        // 配列の場合はnullでも空配列を返す
        if (Str::endsWith($docType, '[]')) {
            $type = substr($docType, 0, -2);
            // 配列で指定されている場合は、collectionにキャストする
            return collect((array)$value)->map(function ($inner) use ($type) {
                return $this->castValue($type, $inner);
            });
        }

        // docTypeを|で分離して複数の型を許容する
        $docTypes = explode('|', $docType);
        // スペースをトリミングし、nullの文字を小文字にする
        $docTypes = array_map(function ($type) {
            $type = trim($type);
            if (\strtolower($type) === 'null') {
                return 'null';
            }
            return $type;
        }, $docTypes);

        // nullが許可されている場合、nullってたらそのまま返しちゃう
        if (is_null($value)) {
            if (
                in_array(
                    'null',
                    $docTypes,
                    true
                )
            ) {
                return null;
            } else {
                // null不許可のため、nullだったらエラー
                throw new InvalidArgumentException("value required but null given");
            }
        }

        // nullではない型を処理の対象とする
        // nullを除いたら一つの型になるはずのため、一つだけ取得する
        $docTypes = array_filter($docTypes, fn ($type) => $type !== 'null');
        $docType = reset($docTypes);

        if ($docType === 'string') {
            return (string) $value;
        }

        if ($docType === 'bool') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        if ($docType === 'int') {
            return (int) $value;
        }

        if ($docType === 'float') {
            return (float) $value;
        }

        // enumの場合は、enumの値を取得する
        // UnitEnumインターフェイスのサブクラスかどうかで判定する
        if (is_subclass_of($docType, UnitEnum::class, true)) {
            return call_user_func([$docType, 'from'], $value);
        }

        // 何も該当しないのはおかしいのでエラー出す
        $type = gettype($value);
        $output = print_r($value, true);
        $message = static::class . ": Expected $docType but $type: '$output' given'";
        throw new InvalidArgumentException($message);
    }

    /**
     * @param string $funcName
     * @param mixed $default
     * @return mixed
     */
    public function __call($funcName, mixed $default = null)
    {
        if (Str::startsWith($funcName, 'get')) {
            // getterの自動取得
            // パラメータはgetterのgetを除いて先頭を小文字にしたものを使用する
            // 例:
            //   getIdToken -> idToken
            $name = lcfirst(substr($funcName, 3));

            $cast = static::docType($name) ?? null;
            if (isset($cast)) {
                return $this->castedInput($name, $cast, $default);
            }
            return $this->inputInternal($name, $default);
        }

        throw new RuntimeException('Undefined method: ' . $funcName);
    }

    /**
     * @param string $name
     * @return string|null 存在しないフィールドの場合nullを返す
     */
    public static function docType(string $name): ?string
    {
        return static::$casts[$name] ?? null;
    }

    ##############################
    # リクエスト情報を取得するためのメソッド
    ##############################

    /**
     * プラットフォーム番号を取得する
     *
     * 定義はUserConstants::PLATFORM_XXXを参照
     *
     * @return integer
     */
    public function platform(): int
    {
        $platform = (int)$this->header(
            System::HEADER_PLATFORM,
        );

        return $platform;
    }

    /**
     * プラットフォーム文字列を取得する
     *
     * 定義はSystem::PLATFORM_STRING_XXXを参照
     *
     * @return string
     */
    public function platformString(): string
    {
        $platform = $this->platform();

        // プラットフォーム向けの文字列から課金基盤向けの文字列に変換する
        return PlatformUtil::convertPlatformToCurrencyPlatform($platform);
    }

    /**
     * 課金基盤用プラットフォーム文字列を取得する
     *
     * 定義はCurrencyConstants::OS_PLATFORM_XXXを参照
     *
     * @return string
     */
    public function billingPlatform(): string
    {
        $platform = $this->header(
            System::HEADER_BILLING_PLATFORM,
        );

        return $platform;
    }

    /**
     * 言語文字列を取得する
     *
     * @return string
     */
    public function language(): string
    {
        return $this->header(System::HEADER_LANGUAGE);
    }

    /**
     * クライアントバージョンを取得する
     *
     * @return string|null
     */
    public function clientVersion(): ?string
    {
        return $this->header(System::CLIENT_VERSION);
    }
}
