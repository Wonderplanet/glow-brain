<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $usr_user_id
 * @property int $login_count
 * @property int $is_day_first_login
 * @property int $login_day_count
 * @property int $login_continue_day_count
 * @property int $comeback_day_count
 */
class LogLogin extends LogModel
{
    use HasFactory;

    public function setLoginCount(int $loginCount): void
    {
        $this->login_count = $loginCount;
    }

    public function setIsDayFirstLogin(bool $isDayFirstLogin): void
    {
        $this->is_day_first_login = (int)$isDayFirstLogin;
    }
    public function setLoginDayCount(int $loginDayCount): void
    {
        $this->login_day_count = $loginDayCount;
    }
    public function setLoginContinueDayCount(int $loginContinueDayCount): void
    {
        $this->login_continue_day_count = $loginContinueDayCount;
    }
    public function setComebackDayCount(int $comebackDayCount): void
    {
        $this->comeback_day_count = $comebackDayCount;
    }
}
