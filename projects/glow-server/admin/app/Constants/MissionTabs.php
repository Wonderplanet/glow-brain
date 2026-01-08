<?php

namespace App\Constants;

enum MissionTabs: string
{
    case MISSION_ACHIEVEMENT = 'アチーブメントミッション';
    case MISSION_BEGINNER = '初心者ミッション';
    case MISSION_DAILY = 'デイリーミッション';
    case MISSION_WEEKLY = 'ウィークリーミッション';
    case MISSION_EVENT = 'イベントミッション';
    case MISSION_EVENT_DAILY = 'イベントデイリーミッション';
    case MISSION_DAILY_BONUS = 'ログインボーナス';
    case MISSION_EVENT_DAILY_BONUS = 'イベントログインボーナス';
    case MISSION_LIMITED_TERM = '期間限定ミッション';
}
