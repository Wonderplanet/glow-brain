class GameTranslator
  def self.translate(game_model)
    view_model = GameViewModel.new
    view_model.user = game_model.user
    view_model.character_unison_points = game_model.character_unison_points
    view_model.episodes = game_model.episodes
    view_model.quests = game_model.quests
    view_model.day_quests = game_model.day_quests
    view_model.event_normal_quests = game_model.event_normal_quests
    view_model.character_variants = game_model.character_variants
    view_model.skill_tree_node_releases = game_model.skill_tree_node_releases
    view_model.items = game_model.items
    view_model.musical_units = game_model.musical_units
    view_model.session_category = game_model.session_category
    view_model.solo_story_episodes = game_model.solo_story_episodes
    view_model.group_story_episodes = game_model.group_story_episodes
    view_model.character_variant_voices = game_model.character_variant_voices
    view_model.character_voices = game_model.character_voices
    view_model.normal_missions = game_model.normal_missions
    view_model.daily_missions = game_model.daily_missions
    view_model.event_missions = game_model.event_missions
    view_model.beginner_missions = game_model.beginner_missions
    view_model.updated_mission = game_model.updated_mission
    view_model.in_app_purchase_histories = game_model.in_app_purchase_histories
    view_model.subscription_passes = game_model.subscription_passes
    view_model.gachas = game_model.gachas
    view_model.gacha_sale_histories = game_model.gacha_sale_histories
    view_model.shop_limited_purchase_histories = game_model.shop_limited_purchase_histories
    view_model.mst_released_music_ids = game_model.mst_released_music_ids
    view_model.home_music_list = game_model.home_music_list
    view_model.normal_song_music_list = game_model.normal_song_music_list
    view_model.last_song_music_list = game_model.last_song_music_list
    view_model.mini_stories = game_model.mini_stories
    view_model.supplemental_tutorials = game_model.supplemental_tutorials
    view_model.event_story_episodes = game_model.event_story_episodes
    view_model.main_story_read_campaign_rewards = game_model.main_story_read_campaign_rewards
    view_model.gacha_oha_histories = game_model.gacha_oha_histories
    view_model
  end
end
