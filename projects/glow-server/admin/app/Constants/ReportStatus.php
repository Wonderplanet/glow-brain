<?php

declare(strict_types=1);

namespace App\Constants;

enum ReportStatus: int
{
    case BEFORE_REPORTING = 0;
    case REPORTED = 1;
}
