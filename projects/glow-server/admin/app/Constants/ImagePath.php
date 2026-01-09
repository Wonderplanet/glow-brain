<?php

declare(strict_types=1);

namespace App\Constants;

enum ImagePath: string
{
    case ITEM_PATH = 'item_icon/item_icon/item_icon_';
    case CHARACTER_FRAGMENT_PATH = 'item_icon_piece/item_icon_piece/item_icon_';
    case EMBLEM_PATH = 'emblem_icon/emblem_icon/emblem_icon_';
    case UNIT_PATH = 'unit_icon/unit_icon/unit_icon_';
    case UNIT_TUTORIAL_PATH = 'unit_icon_tutorial/unit_icon_';
    case RESOURCE_PATH = 'player_resource_icon/player_resource_icon_';
    case EXP_PATH = 'player_resource_icon/player_resource_icon_user_';
    case FRAME_PATH = 'image/frame/BgItemIconFrame_';
    case QUEST_PATH = 'quest_image/quest_image/quest_image_';
    case QUEST_EVENT_PATH = 'quest_image/quest_image/event/quest_image_';
    case ARTWORK_PATH = 'artwork_a/artwork_a/artwork_';
    case ARTWORK_FRAGMENT_PATH = 'artwork_fragment/artwork_fragment_icon_';
    case GACHA_BANNER_PATH = 'gachabanner/gacha_banner_';
    case GACHA_LOGO_BANNER_PATH = 'series_logo/series_logo/series_logo_';
    case GACHA_BACKGROUND_BANNER_PATH = 'gacha_top_cutin/gacha_top_cutin_';
    case UNIT_ENEMY_ICON_PATH = 'unit_enemy_icon/unit_enemy_icon/unit_enemy_icon_';
    case KOMA_BG_PATH = 'koma_background/koma_background/koma_background_';
    case SHOP_PASS_ICON = 'shop_pass_icon/shop_pass_icon_';
}
