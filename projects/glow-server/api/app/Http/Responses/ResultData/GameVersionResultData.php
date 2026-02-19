<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

class GameVersionResultData
{
    public function __construct(
        public string $mstHash,
        public string $mstPath,
        public ?string $mstI18nHash,
        public string $mstI18nPath,
        public string $oprHash,
        public string $oprPath,
        public ?string $oprI18nHash,
        public string $oprI18nPath,
        public string $assetCatalogDataPath,
        public string $assetHash,
        public int $tosVersion,
        public int $tosUserAgreeVersion,
        public string $tosUrl,
        public int $privacyPolicyVersion,
        public int $privacyPolicyUserAgreeVersion,
        public string $privacyPolicyUrl,
        public int $globalCnsntVersion,
        public int $globalCnsntUserAgreeVersion,
        public string $globalCnsntUrl,
        public int $iaaVersion,
        public int $iaaUserAgreeVersion,
        public string $iaaUrl,
    ) {
    }
}
