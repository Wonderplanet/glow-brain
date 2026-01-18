class GameConfigTranslator
  def self.translate(game_config_model)
    view_model = GameConfigViewModel.new
    view_model.daily_refresh_hour = game_config_model.daily_refresh_hour
    view_model.musical_unit_required_rank = game_config_model.musical_unit_required_rank
    view_model.character_variant_base_max_level_n = game_config_model.character_variant_base_max_level_n
    view_model.character_variant_base_max_level_r = game_config_model.character_variant_base_max_level_r
    view_model.character_variant_base_max_level_sr = game_config_model.character_variant_base_max_level_sr
    view_model.character_variant_base_max_level_ssr = game_config_model.character_variant_base_max_level_ssr
    view_model.max_level_per_limit_break = game_config_model.max_level_per_limit_break
    view_model.character_variant_max_level = game_config_model.character_variant_max_level
    view_model.max_send_friend_request_count = game_config_model.max_send_friend_request_count
    view_model.max_receive_friend_request_count = game_config_model.max_receive_friend_request_count
    view_model.present_box_count = game_config_model.present_box_count
    view_model.present_box_keeping_day = game_config_model.present_box_keeping_day
    view_model.max_tp_amount = game_config_model.max_tp_amount
    view_model.max_up_amount = game_config_model.max_up_amount
    view_model.max_memory_tree_material_amount = game_config_model.max_memory_tree_material_amount
    view_model.max_gacha_ticket_amount = game_config_model.max_gacha_ticket_amount
    view_model.max_ap_recover_amount = game_config_model.max_ap_recover_amount
    view_model.over_limit_break_memory_duplicate_point_n = game_config_model.over_limit_break_memory_duplicate_point_n
    view_model.over_limit_break_memory_duplicate_point_r = game_config_model.over_limit_break_memory_duplicate_point_r
    view_model.over_limit_break_memory_duplicate_point_sr = game_config_model.over_limit_break_memory_duplicate_point_sr
    view_model.over_limit_break_memory_duplicate_point_ssr = game_config_model.over_limit_break_memory_duplicate_point_ssr
    view_model.ap_recover_second = game_config_model.ap_recover_second
    view_model.ap_recover_crystal = game_config_model.ap_recover_crystal
    view_model.inter_song_percentage_r = game_config_model.inter_song_percentage_r
    view_model.inter_song_percentage_sr = game_config_model.inter_song_percentage_sr
    view_model.max_ap_boost_count = game_config_model.max_ap_boost_count
    view_model.max_beginner_login_bonus_day = game_config_model.max_beginner_login_bonus_day
    view_model.max_memory_duplicate_point_amount = game_config_model.max_memory_duplicate_point_amount
    view_model.max_ap_recover_limit = game_config_model.max_ap_recover_limit
    view_model.puzzle_continue_crystal_amount = game_config_model.puzzle_continue_crystal_amount
    view_model.mini_story_release_crystal_amount = game_config_model.mini_story_release_crystal_amount
    view_model.shop_month_limit_under16 = game_config_model.shop_month_limit_under16
    view_model.shop_month_limit16to19 = game_config_model.shop_month_limit16to19
    view_model.max_user_nick_name_length = game_config_model.max_user_nick_name_length
    view_model.max_user_scenario_name_length = game_config_model.max_user_scenario_name_length
    view_model.max_user_description_length = game_config_model.max_user_description_length
    view_model.monthly_purchase_alert_boundary = game_config_model.monthly_purchase_alert_boundary
    view_model.special_quest_daily_play_count = game_config_model.special_quest_daily_play_count
    view_model.solo_live_boost_item_category = game_config_model.solo_live_boost_item_category
    view_model.solo_live_turn_boost_item_consumption = game_config_model.solo_live_turn_boost_item_consumption
    view_model.solo_live_fever_boost_item_consumption = game_config_model.solo_live_fever_boost_item_consumption
    view_model.solo_live_appeal_bit_boost_item_consumption = game_config_model.solo_live_appeal_bit_boost_item_consumption
    view_model
  end
end
