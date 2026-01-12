require 'rails_helper'

RSpec.describe "GameTranslator" do
  subject { GameTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, user: 0, character_unison_points: 1, episodes: 2, quests: 3, day_quests: 4, event_normal_quests: 5, character_variants: 6, skill_tree_node_releases: 7, items: 8, musical_units: 9, session_category: 10, solo_story_episodes: 11, group_story_episodes: 12, character_variant_voices: 13, character_voices: 14, normal_missions: 15, daily_missions: 16, event_missions: 17, beginner_missions: 18, updated_mission: 19, in_app_purchase_histories: 20, subscription_passes: 21, gachas: 22, gacha_sale_histories: 23, shop_limited_purchase_histories: 24, mst_released_music_ids: 25, home_music_list: 26, normal_song_music_list: 27, last_song_music_list: 28, mini_stories: 29, supplemental_tutorials: 30, event_story_episodes: 31, main_story_read_campaign_rewards: 32, gacha_oha_histories: 33)}

    it do
      view_model = subject
      expect(view_model.is_a?(GameViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.user).to eq use_case_data.user
      expect(view_model.character_unison_points).to eq use_case_data.character_unison_points
      expect(view_model.episodes).to eq use_case_data.episodes
      expect(view_model.quests).to eq use_case_data.quests
      expect(view_model.day_quests).to eq use_case_data.day_quests
      expect(view_model.event_normal_quests).to eq use_case_data.event_normal_quests
      expect(view_model.character_variants).to eq use_case_data.character_variants
      expect(view_model.skill_tree_node_releases).to eq use_case_data.skill_tree_node_releases
      expect(view_model.items).to eq use_case_data.items
      expect(view_model.musical_units).to eq use_case_data.musical_units
      expect(view_model.session_category).to eq use_case_data.session_category
      expect(view_model.solo_story_episodes).to eq use_case_data.solo_story_episodes
      expect(view_model.group_story_episodes).to eq use_case_data.group_story_episodes
      expect(view_model.character_variant_voices).to eq use_case_data.character_variant_voices
      expect(view_model.character_voices).to eq use_case_data.character_voices
      expect(view_model.normal_missions).to eq use_case_data.normal_missions
      expect(view_model.daily_missions).to eq use_case_data.daily_missions
      expect(view_model.event_missions).to eq use_case_data.event_missions
      expect(view_model.beginner_missions).to eq use_case_data.beginner_missions
      expect(view_model.updated_mission).to eq use_case_data.updated_mission
      expect(view_model.in_app_purchase_histories).to eq use_case_data.in_app_purchase_histories
      expect(view_model.subscription_passes).to eq use_case_data.subscription_passes
      expect(view_model.gachas).to eq use_case_data.gachas
      expect(view_model.gacha_sale_histories).to eq use_case_data.gacha_sale_histories
      expect(view_model.shop_limited_purchase_histories).to eq use_case_data.shop_limited_purchase_histories
      expect(view_model.mst_released_music_ids).to eq use_case_data.mst_released_music_ids
      expect(view_model.home_music_list).to eq use_case_data.home_music_list
      expect(view_model.normal_song_music_list).to eq use_case_data.normal_song_music_list
      expect(view_model.last_song_music_list).to eq use_case_data.last_song_music_list
      expect(view_model.mini_stories).to eq use_case_data.mini_stories
      expect(view_model.supplemental_tutorials).to eq use_case_data.supplemental_tutorials
      expect(view_model.event_story_episodes).to eq use_case_data.event_story_episodes
      expect(view_model.main_story_read_campaign_rewards).to eq use_case_data.main_story_read_campaign_rewards
      expect(view_model.gacha_oha_histories).to eq use_case_data.gacha_oha_histories
    end
  end
end
