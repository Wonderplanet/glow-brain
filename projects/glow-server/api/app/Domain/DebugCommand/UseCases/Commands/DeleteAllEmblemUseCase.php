<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\User\Models\UsrUserProfile;

class DeleteAllEmblemUseCase extends BaseCommands
{
    protected string $name = '所持エンブレムの一斉削除';
    protected string $description = '所持しているエンブレムを一斉削除します。';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: 所持しているエンブレムを一斉削除します
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        $usrUserProfile = UsrUserProfile::query()->where('usr_user_id', $user->id)->first();

        if ($usrUserProfile->getMstEmblemId()) {
            //ユーザーの設定しているエンブレムを削除
            UsrUserProfile::query()
                ->where('usr_user_id', $user->id)
                ->update([
                    'mst_emblem_id' => '',
                ]);
        }

        //ユーザーの所持しているエンブレムを削除
        UsrEmblem::query()
            ->where('usr_user_id', $user->id)
            ->delete();
    }
}
