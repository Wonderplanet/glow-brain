require 'rails_helper'

RSpec.describe "MstMainQuestTranslator" do
  subject { MstMainQuestTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_main_quest_chapter_id: 1, name: 2, stamina_consumption: 3, recommended_level: 4, mst_main_quest_puzzle_stage_id: 5, dependency_mst_main_quest_id: 6, prize: 7, release_at: 8)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstMainQuestViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_main_quest_chapter_id).to eq use_case_data.mst_main_quest_chapter_id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.stamina_consumption).to eq use_case_data.stamina_consumption
      expect(view_model.recommended_level).to eq use_case_data.recommended_level
      expect(view_model.mst_main_quest_puzzle_stage_id).to eq use_case_data.mst_main_quest_puzzle_stage_id
      expect(view_model.dependency_mst_main_quest_id).to eq use_case_data.dependency_mst_main_quest_id
      expect(view_model.prize).to eq use_case_data.prize
      expect(view_model.release_at).to eq use_case_data.release_at
    end
  end
end
