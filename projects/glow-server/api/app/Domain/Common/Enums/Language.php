<?php

declare(strict_types=1);

namespace App\Domain\Common\Enums;

/**
 * 言語
 */
enum Language: string
{
    /**
     * 日本語
     */
    case Ja = 'ja';

    /**
     * English
     */
    case En = 'en';

    /**
     * 繁体字
     */
    case Zh_Hant = 'zh-Hant';
}
