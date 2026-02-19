<?php

declare(strict_types=1);

namespace App\Domain\Resource\Sys\Models;

use App\Domain\Resource\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model as BaseModel;

/**
 * sys系テーブルのModelで使用する
 * MstModelをベースに作成
 */
abstract class SysModel extends BaseModel
{
    use HasFactory;

    /**
     * 主キーはUUIDを採用する可能性があるためstring型を指定
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 主キーはUUIDを採用するため自動incrementを無効化する
     * @var bool
     */
    public $incrementing = false;
}
