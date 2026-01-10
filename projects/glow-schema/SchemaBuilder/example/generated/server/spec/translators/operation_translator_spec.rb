require 'rails_helper'

RSpec.describe "OperationTranslator" do
  subject { OperationTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_home_banners: 0, opr_gachas: 1, opr_gacha_sales: 2, opr_stepup_gachas: 3, opr_stepup_gacha_steps: 4, opr_in_app_products: 5, opr_in_app_product_items: 6, opr_in_app_product_crystals: 7, opr_items: 8, opr_shop_categories: 9, opr_shops: 10, opr_shop_items: 11, opr_campaigns: 12, opr_login_bonuses: 13, opr_login_bonus_rewards: 14, opr_login_popups: 15, opr_mini_stories: 16, opr_events: 17, opr_tutorial_gachas: 18, opr_event_point_rewards: 19, opr_event_ranking_rewards: 20, opr_point_up_character_variants: 21, opr_event_normal_quests: 22, opr_event_normal_quest_puzzle_stages: 23, opr_event_nquest_pstage_songs: 24, opr_event_nquest_pstage_opponents: 25, opr_event_special_quests: 26, opr_event_special_quest_puzzle_stages: 27, opr_event_squest_pstage_songs: 28, opr_event_squest_pstage_opponents: 29, opr_event_guerrilla_quests: 30, opr_event_guerrilla_quest_puzzle_stages: 31, opr_event_gquest_pstage_songs: 32, opr_event_gquest_pstage_opponents: 33, opr_event_missions: 34, opr_main_story_read_campaigns: 35, opr_main_story_read_campaign_rewards: 36, opr_point_up_times: 37, opr_solo_lives: 38)}

    it do
      view_model = subject
      expect(view_model.is_a?(OperationViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_home_banners).to eq use_case_data.opr_home_banners
      expect(view_model.opr_gachas).to eq use_case_data.opr_gachas
      expect(view_model.opr_gacha_sales).to eq use_case_data.opr_gacha_sales
      expect(view_model.opr_stepup_gachas).to eq use_case_data.opr_stepup_gachas
      expect(view_model.opr_stepup_gacha_steps).to eq use_case_data.opr_stepup_gacha_steps
      expect(view_model.opr_in_app_products).to eq use_case_data.opr_in_app_products
      expect(view_model.opr_in_app_product_items).to eq use_case_data.opr_in_app_product_items
      expect(view_model.opr_in_app_product_crystals).to eq use_case_data.opr_in_app_product_crystals
      expect(view_model.opr_items).to eq use_case_data.opr_items
      expect(view_model.opr_shop_categories).to eq use_case_data.opr_shop_categories
      expect(view_model.opr_shops).to eq use_case_data.opr_shops
      expect(view_model.opr_shop_items).to eq use_case_data.opr_shop_items
      expect(view_model.opr_campaigns).to eq use_case_data.opr_campaigns
      expect(view_model.opr_login_bonuses).to eq use_case_data.opr_login_bonuses
      expect(view_model.opr_login_bonus_rewards).to eq use_case_data.opr_login_bonus_rewards
      expect(view_model.opr_login_popups).to eq use_case_data.opr_login_popups
      expect(view_model.opr_mini_stories).to eq use_case_data.opr_mini_stories
      expect(view_model.opr_events).to eq use_case_data.opr_events
      expect(view_model.opr_tutorial_gachas).to eq use_case_data.opr_tutorial_gachas
      expect(view_model.opr_event_point_rewards).to eq use_case_data.opr_event_point_rewards
      expect(view_model.opr_event_ranking_rewards).to eq use_case_data.opr_event_ranking_rewards
      expect(view_model.opr_point_up_character_variants).to eq use_case_data.opr_point_up_character_variants
      expect(view_model.opr_event_normal_quests).to eq use_case_data.opr_event_normal_quests
      expect(view_model.opr_event_normal_quest_puzzle_stages).to eq use_case_data.opr_event_normal_quest_puzzle_stages
      expect(view_model.opr_event_nquest_pstage_songs).to eq use_case_data.opr_event_nquest_pstage_songs
      expect(view_model.opr_event_nquest_pstage_opponents).to eq use_case_data.opr_event_nquest_pstage_opponents
      expect(view_model.opr_event_special_quests).to eq use_case_data.opr_event_special_quests
      expect(view_model.opr_event_special_quest_puzzle_stages).to eq use_case_data.opr_event_special_quest_puzzle_stages
      expect(view_model.opr_event_squest_pstage_songs).to eq use_case_data.opr_event_squest_pstage_songs
      expect(view_model.opr_event_squest_pstage_opponents).to eq use_case_data.opr_event_squest_pstage_opponents
      expect(view_model.opr_event_guerrilla_quests).to eq use_case_data.opr_event_guerrilla_quests
      expect(view_model.opr_event_guerrilla_quest_puzzle_stages).to eq use_case_data.opr_event_guerrilla_quest_puzzle_stages
      expect(view_model.opr_event_gquest_pstage_songs).to eq use_case_data.opr_event_gquest_pstage_songs
      expect(view_model.opr_event_gquest_pstage_opponents).to eq use_case_data.opr_event_gquest_pstage_opponents
      expect(view_model.opr_event_missions).to eq use_case_data.opr_event_missions
      expect(view_model.opr_main_story_read_campaigns).to eq use_case_data.opr_main_story_read_campaigns
      expect(view_model.opr_main_story_read_campaign_rewards).to eq use_case_data.opr_main_story_read_campaign_rewards
      expect(view_model.opr_point_up_times).to eq use_case_data.opr_point_up_times
      expect(view_model.opr_solo_lives).to eq use_case_data.opr_solo_lives
    end
  end
end
