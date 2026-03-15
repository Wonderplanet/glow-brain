<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

class UsrTutorialData
{
    public function __construct(
        public string $mstTutorialFunctionName,
    ) {
    }
}
