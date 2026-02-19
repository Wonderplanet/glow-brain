<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Repositories;

use App\Domain\Encyclopedia\Models\LogArtworkFragment;
use App\Domain\Resource\Log\Repositories\LogModelRepository;

class LogArtworkFragmentRepository extends LogModelRepository
{
    protected string $modelClass = LogArtworkFragment::class;

    public function create(
        string $usrUserId,
        string $mstArtworkFragmentId,
        string $contentType,
        string $targetId,
        bool $isCompleteArtwork
    ): LogArtworkFragment {
        $model = $this->make(
            $usrUserId,
            $mstArtworkFragmentId,
            $contentType,
            $targetId,
            $isCompleteArtwork
        );

        $this->addModel($model);

        return $model;
    }

    public function make(
        string $usrUserId,
        string $mstArtworkFragmentId,
        string $contentType,
        string $targetId,
        bool $isCompleteArtwork
    ): LogArtworkFragment {
        $model = new LogArtworkFragment();
        $model->setUsrUserId($usrUserId);
        $model->setMstArtworkFragmentId($mstArtworkFragmentId);
        $model->setContentType($contentType);
        $model->setTargetId($targetId);
        $model->setIsCompleteArtwork($isCompleteArtwork);

        return $model;
    }
}
