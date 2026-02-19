<?php

declare(strict_types=1);

namespace App\Domain\User\Enums;

enum BnidLinkActionType: string
{
    case LINK_FROM_HOME = 'LinkFromHome';
    case LINK_FROM_TITLE = 'LinkFromTitle';
    case UNLINK = 'Unlink';
}
