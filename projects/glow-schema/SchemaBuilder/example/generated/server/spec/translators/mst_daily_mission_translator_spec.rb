require 'rails_helper'

RSpec.describe "MstDailyMissionTranslator" do
  subject { MstDailyMissionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, day_of_week: 0, mission_number: 1, category: 2, description: 3, goal_count: 4, prize: 5, mst_character_variant_id: 6, mst_day_quest_id: 7, mst_character_id: 8)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstDailyMissionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.day_of_week).to eq use_case_data.day_of_week
      expect(view_model.mission_number).to eq use_case_data.mission_number
      expect(view_model.category).to eq use_case_data.category
      expect(view_model.description).to eq use_case_data.description
      expect(view_model.goal_count).to eq use_case_data.goal_count
      expect(view_model.prize).to eq use_case_data.prize
      expect(view_model.mst_character_variant_id).to eq use_case_data.mst_character_variant_id
      expect(view_model.mst_day_quest_id).to eq use_case_data.mst_day_quest_id
      expect(view_model.mst_character_id).to eq use_case_data.mst_character_id
    end
  end
end
