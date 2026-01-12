class OperationTranslator
  def self.translate(operation_model)
    view_model = OperationViewModel.new
    view_model.opr_home_banners = operation_model.opr_home_banners
    view_model.opr_gachas = operation_model.opr_gachas
    view_model.opr_gacha_sales = operation_model.opr_gacha_sales
    view_model.opr_stepup_gachas = operation_model.opr_stepup_gachas
    view_model.opr_stepup_gacha_steps = operation_model.opr_stepup_gacha_steps
    view_model.opr_in_app_products = operation_model.opr_in_app_products
    view_model.opr_in_app_product_items = operation_model.opr_in_app_product_items
    view_model.opr_in_app_product_crystals = operation_model.opr_in_app_product_crystals
    view_model.opr_items = operation_model.opr_items
    view_model.opr_shop_categories = operation_model.opr_shop_categories
    view_model.opr_shops = operation_model.opr_shops
    view_model.opr_shop_items = operation_model.opr_shop_items
    view_model.opr_campaigns = operation_model.opr_campaigns
    view_model.opr_login_bonuses = operation_model.opr_login_bonuses
    view_model.opr_login_bonus_rewards = operation_model.opr_login_bonus_rewards
    view_model.opr_login_popups = operation_model.opr_login_popups
    view_model.opr_mini_stories = operation_model.opr_mini_stories
    view_model.opr_events = operation_model.opr_events
    view_model.opr_tutorial_gachas = operation_model.opr_tutorial_gachas
    view_model.opr_event_point_rewards = operation_model.opr_event_point_rewards
    view_model.opr_event_ranking_rewards = operation_model.opr_event_ranking_rewards
    view_model.opr_point_up_character_variants = operation_model.opr_point_up_character_variants
    view_model.opr_event_normal_quests = operation_model.opr_event_normal_quests
    view_model.opr_event_normal_quest_puzzle_stages = operation_model.opr_event_normal_quest_puzzle_stages
    view_model.opr_event_nquest_pstage_songs = operation_model.opr_event_nquest_pstage_songs
    view_model.opr_event_nquest_pstage_opponents = operation_model.opr_event_nquest_pstage_opponents
    view_model.opr_event_special_quests = operation_model.opr_event_special_quests
    view_model.opr_event_special_quest_puzzle_stages = operation_model.opr_event_special_quest_puzzle_stages
    view_model.opr_event_squest_pstage_songs = operation_model.opr_event_squest_pstage_songs
    view_model.opr_event_squest_pstage_opponents = operation_model.opr_event_squest_pstage_opponents
    view_model.opr_event_guerrilla_quests = operation_model.opr_event_guerrilla_quests
    view_model.opr_event_guerrilla_quest_puzzle_stages = operation_model.opr_event_guerrilla_quest_puzzle_stages
    view_model.opr_event_gquest_pstage_songs = operation_model.opr_event_gquest_pstage_songs
    view_model.opr_event_gquest_pstage_opponents = operation_model.opr_event_gquest_pstage_opponents
    view_model.opr_event_missions = operation_model.opr_event_missions
    view_model.opr_main_story_read_campaigns = operation_model.opr_main_story_read_campaigns
    view_model.opr_main_story_read_campaign_rewards = operation_model.opr_main_story_read_campaign_rewards
    view_model.opr_point_up_times = operation_model.opr_point_up_times
    view_model.opr_solo_lives = operation_model.opr_solo_lives
    view_model
  end
end
