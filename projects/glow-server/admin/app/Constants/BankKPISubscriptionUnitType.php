<?php

namespace App\Constants;

enum BankKPISubscriptionUnitType: int
{
    case DAY = 1;
    case WEEK = 2;
    case MONTH = 3;
}
