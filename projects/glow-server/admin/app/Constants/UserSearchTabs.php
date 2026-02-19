<?php

namespace App\Constants;

enum UserSearchTabs: string
{
    case USER_PARAMETER = 'プレイヤーパラメーター';
    case TUTORIAL = 'チュートリアル';
    case QUEST = 'クエスト';
    case EVENT_QUEST = 'イベントクエスト';
    case ENHANCE_QUEST = 'コイン獲得クエスト';
    case UNLOCK_STAGE = 'ステージ開放';
    case IDLE_INCENTIVE = '探索';
    case UNIT = 'キャラ';
    case ITEM = 'アイテム';
    case SHOP_BASIC = 'ショップ';
    case SHOP_PURCHASE = '課金ショップ';
    case SHOP_PASS = 'パス';
    case EMBLEM = 'エンブレム';
    case ARTWORK = '原画';
    case ENCYCLOPEDIA_RANK = '図鑑ランク報酬';
    case GACHA = 'ガシャ';
    case BOX_GACHA = 'BOXガシャ';
    case OUTPOST = 'ゲート';
    case MAIL_BOX = 'メールBOX';
    case PARTY = 'パーティ編成';
    case ADVENT_BATTLE = '降臨バトル';
    case PVP = 'ランクマッチ';
    case JUMP_PLUS_REWARD = 'ジャンプ+連携報酬';
    case SUSPECTED = '不正疑惑';
    case EXCHANGE = '交換所';

    // ミッション
    case MISSION_ACHIEVEMENT = 'アチーブメントミッション';
    case MISSION_BEGINNER = '初心者ミッション';
    case MISSION_DAILY = 'デイリーミッション';
    case MISSION_WEEKLY = 'ウィークリーミッション';
    case MISSION_DAILY_BONUS = 'ログインボーナス';
    case MISSION_EVENT = 'イベントミッション';
    case MISSION_EVENT_DAILY = 'イベントデイリーミッション';
    case MISSION_EVENT_DAILY_BONUS = 'イベントログインボーナス';
    case MISSION_LIMITED_TERM = '期間限定ミッション';
    case COMEBACK_BONUS = 'カムバックボーナス';

    //ログ
    case LOG_COIN = 'コイン履歴';
    case LOG_EXP = '経験値履歴';
    case LOG_STAMINA = 'スタミナ履歴';
    case LOG_ITEM = 'アイテム履歴';
    case LOG_EMBLEM = 'エンブレム履歴';
    case LOG_GIFT = 'ギフト履歴';
    case LOG_UNIT_RANK_UP = 'キャラランクアップ履歴';
    case LOG_UNIT_LEVEL_UP = 'キャラレベルアップ履歴';
    case LOG_UNIT_GRADE_UP = 'キャラグレードアップ履歴';
    case LOG_CURRENCY_PAID = '有償プリズム履歴';
    case LOG_CURRENCY_FREE = '無償プリズム履歴';
    case LOG_STAGE_ACTION = 'ステージ履歴';
    case LOG_STORE = 'ショップ履歴';
    case LOG_ADVENT_BATTLE_ACTION = '降臨バトル履歴';
    case LOG_PVP_ACTION = 'ランクマッチ履歴';
    case LOG_GACHA_ACTION = 'ガシャ履歴';
    case LOG_BOX_GACHA_ACTION = 'BOXガシャ履歴';
    case LOG_BNID_LINK = '引き継ぎ履歴';
    case LOG_OUTPOST_ENHANCEMENT = 'ゲート強化履歴';
    case LOG_LOGIN = 'ログイン履歴';
    case LOG_SUSPECTED_USER = 'アカウント停止履歴';
    case LOG_TRADE_SHOP_ITEM = 'ショップ交換履歴';
    case LOG_ARTWORK_FRAGMENT = '原画のかけら履歴';
    case LOG_EXCHANGE = '交換所履歴';
}
