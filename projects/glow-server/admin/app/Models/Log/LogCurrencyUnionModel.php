<?php

declare(strict_types=1);

namespace App\Models\Log;

use App\Constants\Database;
use App\Models\Usr\UsrUser;
use Illuminate\Database\Eloquent\Model;

class LogCurrencyUnionModel extends Model
{
    protected $connection = Database::TIDB_CONNECTION;

    /**
     * UNIONした結果を扱うため、テーブル名は設定しない
     *
     * @var string
     */
    protected $table = '';

    /**
     * 主キーが整数でないため、自動incrementを無効化する
     *
     * @var boolean
     */
    public $incrementing = false;

    /**
     * 主キーの型をstringにする
     *
     * @var string
     */
    protected $keyType = 'string';

    // リレーション

    /**
     * ユーザー情報
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(UsrUser::class, 'usr_user_id', 'id');
    }
}
