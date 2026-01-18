<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Models;

use App\Domain\Constants\Database;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use stdClass;

/** @phpstan-consistent-constructor */
abstract class UsrModel implements UsrModelInterface
{
    protected static string $connection = Database::TIDB_CONNECTION;

    protected static string $tableName = '';

    /**
     * モデルの最新の状態を示すカラムの値を保持する
     * @var array<mixed>
     */
    protected array $attributes = [];

    /**
     * モデルインスタンス生成時の各カラムの値を保持する
     * @var array<mixed>
     */
    protected array $original = [];

    /**
     * 他のデータと区別するための一意なキーを構成するカラム名。
     * 子クラスのテーブル構造に応じてオーバーライドしてください
     *
     * @var array<string>
     */
    protected array $modelKeyColumns = ['usr_user_id'];

    /**
     * 他のデータと区別するための一意なキー
     * @var string
     */
    protected string $modelKey;

    /**
     * DBにないデータかどうかを示すフラグ。
     * insert対象データがtrue、update対象データがfalseになる。
     *
     * デフォルトではインスタンス生成時に新規作成として扱い、
     * データベースから取得したデータを元にインスタンスを生成した場合にfalseに変更する
     *
     * @var bool true: 新規作成, false: 既存データ
     */
    protected bool $isNew = true;

    /**
     * @param array<mixed> $attributes
     */
    protected function __construct(array $attributes)
    {
        if (!isset($attributes['id'])) {
            $attributes['id'] = self::generateId();
        }

        $this->attributes = $attributes;
        $this->original = $this->attributes;

        $this->setModelKey();
    }

    public static function query(): Builder
    {
        return DB::connection(static::$connection)
            ->table(static::getTableName());
    }

    /**
     * DBから取得したデータを元にインスタンスを生成する。
     * DBに既にあり、新規データではないので、isNewフラグをfalseにする
     *
     * @param \stdClass $object
     * @return UsrModel
     */
    public static function createFromRecord(stdClass $object): UsrModelInterface
    {
        $model = new static((array) $object);
        $model->markAsExisting();
        return $model;
    }

    /**
     * 新規データではないことを示すフラグを立てる
     * @return void
     */
    private function markAsExisting(): void
    {
        $this->isNew = false;
    }

    /**
     * modelKeyを生成してセットする
     * @return void
     */
    private function setModelKey(): void
    {
        $this->modelKey = implode(
            '_',
            array_map(
                fn($column) => $this->attributes[$column],
                $this->modelKeyColumns
            ),
        );
    }

    /**
     * DBのデータと比較して、モデルのカラム1つ以上に変更があるかどうかを判定する。
     * DBにない新規データの場合は変更ありとして扱う。
     *
     * @return bool true: 変更あり, false: 変更なし
     */
    public function isChanged(): bool
    {
        if ($this->isNew) {
            // DBにない新規データの場合は変更ありとして扱い、DB更新が必要とする
            return true;
        }

        foreach ($this->attributes as $key => $value) {
            if (array_key_exists($key, $this->original) && $this->original[$key] !== $value) {
                return true;
            }
        }

        // DB更新なし
        return false;
    }

    /**
     * DB更新を重複して行わないために、変更がない状態にする
     */
    public function syncOriginal(): static
    {
        $this->original = $this->attributes;
        $this->markAsExisting();

        return $this;
    }

    private static function generateId(): string
    {
        return (string) Uuid::uuid4();
    }

    public static function getTableName(): string
    {
        return static::$tableName;
    }

    public function getId(): string
    {
        return $this->attributes['id'];
    }

    public function getUsrUserId(): string
    {
        return $this->attributes['usr_user_id'];
    }

    public function makeModelKey(): string
    {
        return $this->modelKey;
    }
}
