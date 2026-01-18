<?php

namespace App\Models\Mst;

interface IAssetImage
{
    public function makeAssetPath(): ?string;

    public function makeBgPath(): ?string;
}
