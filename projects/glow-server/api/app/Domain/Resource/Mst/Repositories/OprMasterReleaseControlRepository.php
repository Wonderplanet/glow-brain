<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Models\Contracts\OprMasterReleaseControlInterface;
use App\Domain\Resource\Mst\Models\OprMasterReleaseControl;

class OprMasterReleaseControlRepository
{
    private ?OprMasterReleaseControlInterface $activeReleaseControl = null;

    /**
     * @return OprMasterReleaseControlInterface|null
     */
    public function getCurrent(): ?OprMasterReleaseControlInterface
    {
        if (isset($this->activeReleaseControl)) {
            return $this->activeReleaseControl;
        }

        $activeReleaseControl = OprMasterReleaseControl::query()
            ->orderBy('created_at', 'desc')
            ->first();

        $this->activeReleaseControl = $activeReleaseControl;

        return $activeReleaseControl;
    }
}
