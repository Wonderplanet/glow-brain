require 'rails_helper'

RSpec.describe "GameConfigTranslator" do
  subject { GameConfigTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, daily_refresh_hour: 0, musical_unit_required_rank: 1, character_variant_base_max_level_n: 2, character_variant_base_max_level_r: 3, character_variant_base_max_level_sr: 4, character_variant_base_max_level_ssr: 5, max_level_per_limit_break: 6, character_variant_max_level: 7, max_send_friend_request_count: 8, max_receive_friend_request_count: 9, present_box_count: 10, present_box_keeping_day: 11, max_tp_amount: 12, max_up_amount: 13, max_memory_tree_material_amount: 14, max_gacha_ticket_amount: 15, max_ap_recover_amount: 16, over_limit_break_memory_duplicate_point_n: 17, over_limit_break_memory_duplicate_point_r: 18, over_limit_break_memory_duplicate_point_sr: 19, over_limit_break_memory_duplicate_point_ssr: 20, ap_recover_second: 21, ap_recover_crystal: 22, inter_song_percentage_r: 23, inter_song_percentage_sr: 24, max_ap_boost_count: 25, max_beginner_login_bonus_day: 26, max_memory_duplicate_point_amount: 27, max_ap_recover_limit: 28, puzzle_continue_crystal_amount: 29, mini_story_release_crystal_amount: 30, shop_month_limit_under16: 31, shop_month_limit16to19: 32, max_user_nick_name_length: 33, max_user_scenario_name_length: 34, max_user_description_length: 35, monthly_purchase_alert_boundary: 36, special_quest_daily_play_count: 37, solo_live_boost_item_category: 38, solo_live_turn_boost_item_consumption: 39, solo_live_fever_boost_item_consumption: 40, solo_live_appeal_bit_boost_item_consumption: 41, voice_release_crystal_amount: 42)}

    it do
      view_model = subject
      expect(view_model.is_a?(GameConfigViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.daily_refresh_hour).to eq use_case_data.daily_refresh_hour
      expect(view_model.musical_unit_required_rank).to eq use_case_data.musical_unit_required_rank
      expect(view_model.character_variant_base_max_level_n).to eq use_case_data.character_variant_base_max_level_n
      expect(view_model.character_variant_base_max_level_r).to eq use_case_data.character_variant_base_max_level_r
      expect(view_model.character_variant_base_max_level_sr).to eq use_case_data.character_variant_base_max_level_sr
      expect(view_model.character_variant_base_max_level_ssr).to eq use_case_data.character_variant_base_max_level_ssr
      expect(view_model.max_level_per_limit_break).to eq use_case_data.max_level_per_limit_break
      expect(view_model.character_variant_max_level).to eq use_case_data.character_variant_max_level
      expect(view_model.max_send_friend_request_count).to eq use_case_data.max_send_friend_request_count
      expect(view_model.max_receive_friend_request_count).to eq use_case_data.max_receive_friend_request_count
      expect(view_model.present_box_count).to eq use_case_data.present_box_count
      expect(view_model.present_box_keeping_day).to eq use_case_data.present_box_keeping_day
      expect(view_model.max_tp_amount).to eq use_case_data.max_tp_amount
      expect(view_model.max_up_amount).to eq use_case_data.max_up_amount
      expect(view_model.max_memory_tree_material_amount).to eq use_case_data.max_memory_tree_material_amount
      expect(view_model.max_gacha_ticket_amount).to eq use_case_data.max_gacha_ticket_amount
      expect(view_model.max_ap_recover_amount).to eq use_case_data.max_ap_recover_amount
      expect(view_model.over_limit_break_memory_duplicate_point_n).to eq use_case_data.over_limit_break_memory_duplicate_point_n
      expect(view_model.over_limit_break_memory_duplicate_point_r).to eq use_case_data.over_limit_break_memory_duplicate_point_r
      expect(view_model.over_limit_break_memory_duplicate_point_sr).to eq use_case_data.over_limit_break_memory_duplicate_point_sr
      expect(view_model.over_limit_break_memory_duplicate_point_ssr).to eq use_case_data.over_limit_break_memory_duplicate_point_ssr
      expect(view_model.ap_recover_second).to eq use_case_data.ap_recover_second
      expect(view_model.ap_recover_crystal).to eq use_case_data.ap_recover_crystal
      expect(view_model.inter_song_percentage_r).to eq use_case_data.inter_song_percentage_r
      expect(view_model.inter_song_percentage_sr).to eq use_case_data.inter_song_percentage_sr
      expect(view_model.max_ap_boost_count).to eq use_case_data.max_ap_boost_count
      expect(view_model.max_beginner_login_bonus_day).to eq use_case_data.max_beginner_login_bonus_day
      expect(view_model.max_memory_duplicate_point_amount).to eq use_case_data.max_memory_duplicate_point_amount
      expect(view_model.max_ap_recover_limit).to eq use_case_data.max_ap_recover_limit
      expect(view_model.puzzle_continue_crystal_amount).to eq use_case_data.puzzle_continue_crystal_amount
      expect(view_model.mini_story_release_crystal_amount).to eq use_case_data.mini_story_release_crystal_amount
      expect(view_model.shop_month_limit_under16).to eq use_case_data.shop_month_limit_under16
      expect(view_model.shop_month_limit16to19).to eq use_case_data.shop_month_limit16to19
      expect(view_model.max_user_nick_name_length).to eq use_case_data.max_user_nick_name_length
      expect(view_model.max_user_scenario_name_length).to eq use_case_data.max_user_scenario_name_length
      expect(view_model.max_user_description_length).to eq use_case_data.max_user_description_length
      expect(view_model.monthly_purchase_alert_boundary).to eq use_case_data.monthly_purchase_alert_boundary
      expect(view_model.special_quest_daily_play_count).to eq use_case_data.special_quest_daily_play_count
      expect(view_model.solo_live_boost_item_category).to eq use_case_data.solo_live_boost_item_category
      expect(view_model.solo_live_turn_boost_item_consumption).to eq use_case_data.solo_live_turn_boost_item_consumption
      expect(view_model.solo_live_fever_boost_item_consumption).to eq use_case_data.solo_live_fever_boost_item_consumption
      expect(view_model.solo_live_appeal_bit_boost_item_consumption).to eq use_case_data.solo_live_appeal_bit_boost_item_consumption
      expect(view_model.voice_release_crystal_amount).to eq use_case_data.voice_release_crystal_amount
    end
  end
end
