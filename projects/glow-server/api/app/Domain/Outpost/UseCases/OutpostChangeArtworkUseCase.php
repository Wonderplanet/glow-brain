<?php

declare(strict_types=1);

namespace App\Domain\Outpost\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Encyclopedia\Delegators\EncyclopediaDelegator;
use App\Domain\Outpost\Services\UserOutpostService;
use App\Http\Responses\ResultData\OutpostChangeArtworkResultData;

class OutpostChangeArtworkUseCase
{
    use UseCaseTrait;

    public function __construct(
        private UserOutpostService $userOutpostService,
        // Delegator
        private EncyclopediaDelegator $encyclopediaDelegator,
    ) {
    }

    public function exec(CurrentUser $user, string $mstOutpostId, string $mstArtworkId): OutpostChangeArtworkResultData
    {
        $usrUserId = $user->id;
        if (StringUtil::isNotSpecified($mstArtworkId)) {
            $mstArtworkId = null;
        } else {
            $usrArtwork = $this->encyclopediaDelegator->getUsrArtwork($usrUserId, $mstArtworkId);

            // 未所持の原画を設定しようとした場合はエラー
            if (is_null($usrArtwork)) {
                throw new GameException(
                    ErrorCode::ARTWORK_NOT_OWNED,
                    'usr_artwork not found mst_artwork_id: ' . $mstArtworkId
                );
            }
        }

        $usrOutpost = $this->userOutpostService->setArtwork($usrUserId, $mstOutpostId, $mstArtworkId);

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new OutpostChangeArtworkResultData(
            $usrOutpost
        );
    }
}
