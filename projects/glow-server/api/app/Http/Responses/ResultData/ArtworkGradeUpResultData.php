<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Encyclopedia\Models\UsrArtworkInterface;
use Illuminate\Support\Collection;

class ArtworkGradeUpResultData
{
    /**
     * @param UsrArtworkInterface $usrArtwork
     * @param Collection $usrItems
     */
    public function __construct(
        public UsrArtworkInterface $usrArtwork,
        public Collection $usrItems,
    ) {
    }
}
