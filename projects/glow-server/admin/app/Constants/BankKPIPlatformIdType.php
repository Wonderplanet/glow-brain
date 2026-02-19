<?php

namespace App\Constants;

enum BankKPIPlatformIdType: string
{
    case IOS = 'ios';
    case ANDROID = 'android';
    case ASB = 'asb';  // WebStore/アソビストア用 (マルチログイン対応)

    // BanKでは使用可能だが、ここでは使用しないのでコメントアウト
    //case PC = 'pc';
    //case DMM = 'dmm';
    //case STEAM = 'steam';
    //case PS4 = 'ps4';
    //case PS5 = 'ps5';
    //case XSX = 'xsx';
    //case NSW = 'nsw';
    //case WIN = 'win';
}
