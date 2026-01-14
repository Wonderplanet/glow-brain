require 'rails_helper'

RSpec.describe "MstDayQuestTranslator" do
  subject { MstDayQuestTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, name: 1, stamina_consumption: 2, recommended_level: 3, mst_day_quest_puzzle_stage_id: 4, dependency_mst_day_quest_id: 5, prize: 6)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstDayQuestViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.stamina_consumption).to eq use_case_data.stamina_consumption
      expect(view_model.recommended_level).to eq use_case_data.recommended_level
      expect(view_model.mst_day_quest_puzzle_stage_id).to eq use_case_data.mst_day_quest_puzzle_stage_id
      expect(view_model.dependency_mst_day_quest_id).to eq use_case_data.dependency_mst_day_quest_id
      expect(view_model.prize).to eq use_case_data.prize
    end
  end
end
