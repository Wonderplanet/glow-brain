<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Models;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Constants\Database;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids as BaseHasUuids;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Ramsey\Uuid\Uuid;

/**
 * @property string $id
 * @property string $usr_user_id
 */
abstract class UsrEloquentModel extends BaseModel implements UsrModelInterface
{
    use BaseHasUuids;
    use HasFactory;

    protected $connection = Database::TIDB_CONNECTION;

    /**
     * @param array<mixed> $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // UsrModelManagerでのモデル管理に使うmodelKeyがデフォルトではidを使っている
        // そのためidがモデルにsetされていないとエラーになるため、ここで追加しておく
        if (!isset($this->id)) {
            $this->id = $this->newUniqueId();
        }
    }

    /**
     * 主キーはUUIDを採用するためstring
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 主キーはUUIDを採用するため自動incrementを無効化する
     * @var bool
     */
    public $incrementing = false;

    protected $dateFormat = 'Y-m-d H:i:s.';

    /**
     * ModelのtoArrayメソッドを呼んだ際に日付文字列がUTC ISO-8601形式に変換され、
     * DBへの格納時にInvalid datetime formatのエラーが出るので、その対策
     *
     * https://www.larajapan.com/2022/06/28/toarrayでcreated_atupdated_atのフォーマットが変わる
     * https://readouble.com/laravel/9.x/ja/eloquent-mutators.html#date-casting
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat);
    }

    public function newUniqueId(): string
    {
        return (string) Uuid::uuid4();
    }

    /**
     * UsrModelManagerでキャッシュする際のユニークキーを作成する
     */
    public function makeModelKey(): string
    {
        return $this->id;
    }

    public function isChanged(): bool
    {
        return $this->isDirty();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsrUserId(): string
    {
        return $this->usr_user_id;
    }

    public function syncOriginal(): static
    {
        return parent::syncOriginal();
    }

    /**
     * マジックメソッド
     * getterメソッド未実装であってもメソッド名から自動認識して値を返す
     *
     * @param string $method
     * @param array<mixed> $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        // getterメソッド未実装であってもメソッド名から自動認識して値を返す
        if (str_starts_with($method, 'get')) {
            // キャメルケース → スネークケースに変換
            $property = lcfirst(substr($method, 3)); // MstItemId → mstItemId
            $property = \Illuminate\Support\Str::snake($property); // mst_item_id

            // プロパティが存在するかチェック
            if (array_key_exists($property, $this->attributes)) {
                return $this->attributes[$property];
            }

            // おまけ：通常のプロパティ（オーバーロード）対応
            if (property_exists($this, $property)) {
                return $this->$property;
            }

            // 見つからなければ例外投げる
            // throw new \BadMethodCallException("Method {$method} does not exist.");
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                sprintf('method %s does not exist.', $method),
            );
        }

        return parent::__call($method, $parameters);
    }
}
