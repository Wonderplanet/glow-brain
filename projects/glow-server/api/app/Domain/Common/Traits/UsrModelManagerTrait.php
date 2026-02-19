<?php

declare(strict_types=1);

namespace App\Domain\Common\Traits;

use App\Infrastructure\UsrModelManager;

trait UsrModelManagerTrait
{
    public function saveAll(): void
    {
        $usrModelManager = app()->make(UsrModelManager::class);
        $usrModelManager->saveAll();
    }
}
