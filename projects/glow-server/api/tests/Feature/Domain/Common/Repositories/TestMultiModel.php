<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Common\Repositories;

use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property bool $is_changed
 * @property int $int_value
 * @property float $float_value
 * @property string $string_value
 * @property bool $bool_value
 */
class TestMultiModel extends UsrEloquentModel
{
    protected $guarded = [];

    public static function create(
        string $id,
        string $usrUserId,
        bool $isChanged = false,
        int $intValue = 1,
        float $floatValue = 1.,
        string $stringValue = 'string',
        bool $boolValue = true,
    ): TestMultiModel
    {
        $model = new self();
        $model->id = $id;
        $model->usr_user_id = $usrUserId;
        $model->is_changed = $isChanged;
        $model->int_value = $intValue;
        $model->float_value = $floatValue;
        $model->string_value = $stringValue;
        $model->bool_value = $boolValue;

        return $model;
    }

    public function makeModelKey(): string
    {
        return 'modelKey_' . $this->id;
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

    public function getIntValue(): int
    {
        return $this->int_value;
    }

    public function getFloatValue(): float
    {
        return $this->float_value;
    }

    public function getStringValue(): string
    {
        return $this->string_value;
    }

    public function getBoolValue(): bool
    {
        return $this->bool_value;
    }
}
