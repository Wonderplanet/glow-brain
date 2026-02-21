require 'rails_helper'

RSpec.describe "MstNormalMissionTranslator" do
  subject { MstNormalMissionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, mission_number: 0, level: 1, category: 2, description: 3, goal_count: 4, prize: 5, mst_main_quest_id: 6, mst_day_quest_id: 7, mst_character_id: 8, character_variant_level: 9, totu_count: 10, mst_character_variant_id: 11, mst_main_story_episode_id: 12, mst_solo_story_episode_id: 13, mst_group_story_episode_id: 14)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstNormalMissionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.mission_number).to eq use_case_data.mission_number
      expect(view_model.level).to eq use_case_data.level
      expect(view_model.category).to eq use_case_data.category
      expect(view_model.description).to eq use_case_data.description
      expect(view_model.goal_count).to eq use_case_data.goal_count
      expect(view_model.prize).to eq use_case_data.prize
      expect(view_model.mst_main_quest_id).to eq use_case_data.mst_main_quest_id
      expect(view_model.mst_day_quest_id).to eq use_case_data.mst_day_quest_id
      expect(view_model.mst_character_id).to eq use_case_data.mst_character_id
      expect(view_model.character_variant_level).to eq use_case_data.character_variant_level
      expect(view_model.totu_count).to eq use_case_data.totu_count
      expect(view_model.mst_character_variant_id).to eq use_case_data.mst_character_variant_id
      expect(view_model.mst_main_story_episode_id).to eq use_case_data.mst_main_story_episode_id
      expect(view_model.mst_solo_story_episode_id).to eq use_case_data.mst_solo_story_episode_id
      expect(view_model.mst_group_story_episode_id).to eq use_case_data.mst_group_story_episode_id
    end
  end
end
