<?php

namespace Tests\Support\Entities;

use App\Domain\Common\Entities\CurrentUser as EntitiesCurrentUser;
use App\Domain\Common\Enums\UserStatus;

/**
 * テストから利用するダミーのユーザークラス
 * 　UseCaseでUsrUserInterfaceを使用するようにしていたが、互換性のためCurrentUserに戻した経緯があり、
 *   このクラスがテスト向けに残っている。
 *
 * UseCaseによってはInterfaceではなくApp\Domain\Common\Entities\CurrentUserを指定しているため
 * App\Domain\Common\Entities\CurrencyUserを継承している。
 *
 * UsrUserInterfaceはEntities\CurrentUserに実装されている
 */
class CurrentUser extends EntitiesCurrentUser
{
    public function __construct(
        string $id,
        ?string $gameStartAt = null,
        public int $status = UserStatus::NORMAL->value,
        ?string $suspendEndAt = null,
    ) {
        $gameStartAt = $gameStartAt ?? now()->toDateTimeString();

        parent::__construct($id, $gameStartAt, $status, $suspendEndAt);
    }
}
