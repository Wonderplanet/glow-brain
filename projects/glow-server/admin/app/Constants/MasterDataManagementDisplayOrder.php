<?php

namespace App\Constants;

enum MasterDataManagementDisplayOrder: string
{
    case IMPORT_DISPLAY_ORDER = 1;
    case RELEASE_KEY_DISPLAY_ORDER = 2;
    case GIT_APPLY_DISPLAY_ORDER = 3;
}
