<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

class ShopWebstoreUserValidationResultData
{
    public function __construct(
        public string $id,
        public string $internalId,
        public string $name,
        public int $level,
        public string $birthday,
        public string $birthdayMonth,
        public string $country,
    ) {
    }
}
