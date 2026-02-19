<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Constants\Database;
use App\Domain\Resource\Traits\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids as BaseHasUuids;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Str;

abstract class MstModel extends BaseModel
{
    use BaseHasUuids;
    use HasFactory;

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

    /**
     * マスタテーブルには、create_at, update_atは不要なため、デフォルトで無効とする
     * @var bool
     */
    public $timestamps = false;

    protected $connection = Database::MST_CONNECTION;

    /**
     * i18nのテーブル名を考慮した変換を施してテーブル名を取得する
     *
     * i18nテーブルは標準処理では、mst_table_i18nsのようにi18nsとなってしまう。
     * これをmst_tables_i18nの形式に変換し、$tableプロパティにセットするために、
     * EloquentModelのgetTableメソッドをオーバーライドしています。
     */
    public function getTable(): string
    {
        if ($this->table) {
            return $this->table;
        }

        $i18nStr = 'I18n';

        $className = class_basename($this);
        $isI18n = str_ends_with($className, $i18nStr);

        if ($isI18n === false) {
            return Str::snake(Str::pluralStudly($className));
        }

        $tableName = Str::of($className)
            ->replaceLast($i18nStr, '')
            ->plural()
            ->append($i18nStr)
            ->snake()
            ->toString();

        return $tableName;
    }

    public function newUniqueId()
    {
        return (string) Str::orderedUuid();
    }
}
