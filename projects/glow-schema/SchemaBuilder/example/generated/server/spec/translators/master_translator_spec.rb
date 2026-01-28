require 'rails_helper'

RSpec.describe "MasterTranslator" do
  subject { MasterTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, game_config: 0, mst_user_ranks: 1, mst_character_levels: 2, mst_characters: 3, mst_character_variants: 4, mst_character_voices: 5, mst_character_variant_voices: 6, mst_main_story_chapters: 7, mst_main_story_episodes: 8, mst_main_story_episode_characters: 9, mst_group_story_chapters: 10, mst_group_story_episodes: 11, mst_group_story_appear_characters: 12, mst_solo_story_chapters: 13, mst_solo_story_episodes: 14, mst_solo_story_chapter_characters: 15, mst_main_quests: 16, mst_main_quest_chapters: 17, mst_mquest_pstage_opponents: 18, mst_main_quest_puzzle_stages: 19, mst_mquest_pstage_songs: 20, mst_day_quests: 21, mst_day_quest_chapters: 22, mst_day_quest_chapter_quests: 23, mst_puzzle_opponents: 24, mst_puzzle_opponent_actions: 25, mst_dquest_pstage_opponents: 26, mst_day_quest_puzzle_stages: 27, mst_dquest_pstage_songs: 28, mst_drops: 29, mst_items: 30, mst_leader_skills: 31, mst_leader_skill_actions: 32, mst_appeals: 33, mst_appeal_actions: 34, mst_support_skills: 35, mst_support_skill_actions: 36, mst_release_main_story_musics: 37, mst_musics: 38, mst_artist_groups: 39, mst_artist_group_characters: 40, mst_skill_tree_nodes: 41, mst_skill_tree_node_required_items: 42, mst_skill_tree_item_orders: 43, mst_graphic_required_items: 44, mst_normal_missions: 45, mst_daily_missions: 46, mst_beginner_missions: 47, mst_in_app_products: 48, mst_subscription_passes: 49, mst_subscription_pass_rewards: 50, mst_login_bonus_character_variants: 51, mst_mini_story_characters: 52, mst_event_story_episodes: 53, mst_event_story_characters: 54, mst_live_houses: 55, mst_solo_live_drops: 56)}

    it do
      view_model = subject
      expect(view_model.is_a?(MasterViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.game_config).to eq use_case_data.game_config
      expect(view_model.mst_user_ranks).to eq use_case_data.mst_user_ranks
      expect(view_model.mst_character_levels).to eq use_case_data.mst_character_levels
      expect(view_model.mst_characters).to eq use_case_data.mst_characters
      expect(view_model.mst_character_variants).to eq use_case_data.mst_character_variants
      expect(view_model.mst_character_voices).to eq use_case_data.mst_character_voices
      expect(view_model.mst_character_variant_voices).to eq use_case_data.mst_character_variant_voices
      expect(view_model.mst_main_story_chapters).to eq use_case_data.mst_main_story_chapters
      expect(view_model.mst_main_story_episodes).to eq use_case_data.mst_main_story_episodes
      expect(view_model.mst_main_story_episode_characters).to eq use_case_data.mst_main_story_episode_characters
      expect(view_model.mst_group_story_chapters).to eq use_case_data.mst_group_story_chapters
      expect(view_model.mst_group_story_episodes).to eq use_case_data.mst_group_story_episodes
      expect(view_model.mst_group_story_appear_characters).to eq use_case_data.mst_group_story_appear_characters
      expect(view_model.mst_solo_story_chapters).to eq use_case_data.mst_solo_story_chapters
      expect(view_model.mst_solo_story_episodes).to eq use_case_data.mst_solo_story_episodes
      expect(view_model.mst_solo_story_chapter_characters).to eq use_case_data.mst_solo_story_chapter_characters
      expect(view_model.mst_main_quests).to eq use_case_data.mst_main_quests
      expect(view_model.mst_main_quest_chapters).to eq use_case_data.mst_main_quest_chapters
      expect(view_model.mst_mquest_pstage_opponents).to eq use_case_data.mst_mquest_pstage_opponents
      expect(view_model.mst_main_quest_puzzle_stages).to eq use_case_data.mst_main_quest_puzzle_stages
      expect(view_model.mst_mquest_pstage_songs).to eq use_case_data.mst_mquest_pstage_songs
      expect(view_model.mst_day_quests).to eq use_case_data.mst_day_quests
      expect(view_model.mst_day_quest_chapters).to eq use_case_data.mst_day_quest_chapters
      expect(view_model.mst_day_quest_chapter_quests).to eq use_case_data.mst_day_quest_chapter_quests
      expect(view_model.mst_puzzle_opponents).to eq use_case_data.mst_puzzle_opponents
      expect(view_model.mst_puzzle_opponent_actions).to eq use_case_data.mst_puzzle_opponent_actions
      expect(view_model.mst_dquest_pstage_opponents).to eq use_case_data.mst_dquest_pstage_opponents
      expect(view_model.mst_day_quest_puzzle_stages).to eq use_case_data.mst_day_quest_puzzle_stages
      expect(view_model.mst_dquest_pstage_songs).to eq use_case_data.mst_dquest_pstage_songs
      expect(view_model.mst_drops).to eq use_case_data.mst_drops
      expect(view_model.mst_items).to eq use_case_data.mst_items
      expect(view_model.mst_leader_skills).to eq use_case_data.mst_leader_skills
      expect(view_model.mst_leader_skill_actions).to eq use_case_data.mst_leader_skill_actions
      expect(view_model.mst_appeals).to eq use_case_data.mst_appeals
      expect(view_model.mst_appeal_actions).to eq use_case_data.mst_appeal_actions
      expect(view_model.mst_support_skills).to eq use_case_data.mst_support_skills
      expect(view_model.mst_support_skill_actions).to eq use_case_data.mst_support_skill_actions
      expect(view_model.mst_release_main_story_musics).to eq use_case_data.mst_release_main_story_musics
      expect(view_model.mst_musics).to eq use_case_data.mst_musics
      expect(view_model.mst_artist_groups).to eq use_case_data.mst_artist_groups
      expect(view_model.mst_artist_group_characters).to eq use_case_data.mst_artist_group_characters
      expect(view_model.mst_skill_tree_nodes).to eq use_case_data.mst_skill_tree_nodes
      expect(view_model.mst_skill_tree_node_required_items).to eq use_case_data.mst_skill_tree_node_required_items
      expect(view_model.mst_skill_tree_item_orders).to eq use_case_data.mst_skill_tree_item_orders
      expect(view_model.mst_graphic_required_items).to eq use_case_data.mst_graphic_required_items
      expect(view_model.mst_normal_missions).to eq use_case_data.mst_normal_missions
      expect(view_model.mst_daily_missions).to eq use_case_data.mst_daily_missions
      expect(view_model.mst_beginner_missions).to eq use_case_data.mst_beginner_missions
      expect(view_model.mst_in_app_products).to eq use_case_data.mst_in_app_products
      expect(view_model.mst_subscription_passes).to eq use_case_data.mst_subscription_passes
      expect(view_model.mst_subscription_pass_rewards).to eq use_case_data.mst_subscription_pass_rewards
      expect(view_model.mst_login_bonus_character_variants).to eq use_case_data.mst_login_bonus_character_variants
      expect(view_model.mst_mini_story_characters).to eq use_case_data.mst_mini_story_characters
      expect(view_model.mst_event_story_episodes).to eq use_case_data.mst_event_story_episodes
      expect(view_model.mst_event_story_characters).to eq use_case_data.mst_event_story_characters
      expect(view_model.mst_live_houses).to eq use_case_data.mst_live_houses
      expect(view_model.mst_solo_live_drops).to eq use_case_data.mst_solo_live_drops
    end
  end
end
