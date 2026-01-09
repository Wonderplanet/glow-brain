<?php

declare(strict_types=1);

namespace App\Domain\Resource\Enums;

enum RarityType: string
{
    case N = 'N';

    case R = 'R';

    case SR = 'SR';

    case SSR = 'SSR';

    case UR = 'UR';
}
