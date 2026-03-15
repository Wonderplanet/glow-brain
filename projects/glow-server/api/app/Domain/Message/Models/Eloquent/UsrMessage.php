<?php

declare(strict_types=1);

namespace App\Domain\Message\Models\Eloquent;

use App\Domain\Message\Enums\MessageSource;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property string|null $mng_message_id
 * @property MessageSource|null $message_source
 * @property string|null $reward_group_id
 * @property string|null $resource_type
 * @property string|null $resource_id
 * @property int|null $resource_amount
 * @property string|null $title
 * @property string|null $body
 * @property string|null $opened_at
 * @property string|null $received_at
 * @property bool $is_received
 * @property string|null $expired_at
 * @property CarbonImmutable $created_at
 */
class UsrMessage extends UsrEloquentModel
{
}
