<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Models;

use App\Domain\Resource\Log\Models\LogModel;

/**
 * @property string $mst_artwork_fragment_id
 * @property string $content_type
 * @property string $target_id
 * @property int $is_complete_artwork
 */
class LogArtworkFragment extends LogModel
{
    public function setMstArtworkFragmentId(string $mstArtworkFragmentId): void
    {
        $this->mst_artwork_fragment_id = $mstArtworkFragmentId;
    }

    public function setContentType(string $contentType): void
    {
        $this->content_type = $contentType;
    }

    public function setTargetId(string $targetId): void
    {
        $this->target_id = $targetId;
    }

    public function setIsCompleteArtwork(bool $isCompleteArtwork): void
    {
        $this->is_complete_artwork = (int) $isCompleteArtwork;
    }
}
