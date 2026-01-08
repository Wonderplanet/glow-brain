<?php

namespace App\Constants;

enum NavigationGroups: string
{
    case AGGREGATION = '経理・法務サポート';
    case MASTER_DATA_MANAGEMENT = 'マスター管理';
    case CS = '通貨管理';
    case MASTER_DATA_VIEWER = 'マスター参照';
    case CLIENT_ASSET = 'クライアントアセット';
    case DEBUG = 'デバッグ';
    case ADMIN = '管理者';
    case USER = 'プレイヤー';
    case QA_SUPPORT = 'QAサポート';
    case NOTICE = 'お知らせ/ノーティス';
    case MAINTENANCE = 'メンテナンス';
    case BAN = 'BAN';
    case OTHER = 'その他';
}
