<?php

declare(strict_types=1);

namespace App\Domain\Resource\Log\Models;

use App\Domain\Constants\Database;
use App\Domain\Resource\Log\Models\Contracts\LogModelInterface;
use App\Domain\Resource\Traits\HasFactory;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids as BaseHasUuids;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Ramsey\Uuid\Uuid;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property int $logging_no
 * @property string $nginx_request_id
 * @property string $request_id
 */
abstract class LogModel extends BaseModel implements LogModelInterface
{
    use BaseHasUuids;
    use HasFactory;

    protected $connection = Database::TIDB_CONNECTION;

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
     * @param array<mixed> $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // LogModelManagerでのモデル管理に使うmodelKeyがデフォルトではidを使っている
        // そのためidがモデルにsetされていないとエラーになるため、ここで追加しておく
        if (!isset($this->id)) {
            $this->id = $this->newUniqueId();
        }
    }

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
     * LogModelManagerでのモデル管理に使うキー
     * デフォルトではidを使う
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

    public function setUsrUserId(string $usrUserId): void
    {
        $this->usr_user_id = $usrUserId;
    }

    public function formatToInsert(): array
    {
        $values = parent::toArray();

        $now = CarbonImmutable::now();
        $values['created_at'] = $values['created_at'] ?? $now;
        $values['updated_at'] = $values['updated_at'] ?? $now;

        return $values;
    }

    public function setLogging(
        int $loggingNo,
        string $nginxRequestId,
        string $requestId,
    ): void {
        $this->logging_no = $loggingNo;
        $this->nginx_request_id = $nginxRequestId;
        $this->request_id = $requestId;
    }

    public function getLogTableName(): string
    {
        return $this->getTable();
    }
}
