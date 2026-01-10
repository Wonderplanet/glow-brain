<?php

declare(strict_types=1);

namespace App\Domain\Stage\Models;

interface UsrStageInterface extends IBaseUsrStage
{
    public function getClearTimeMs(): ?int;

    public function setClearTimeMs(int $clearTimeMs): void;
}
