require 'rails_helper'

RSpec.describe "MstDquestPstageOpponentTranslator" do
  subject { MstDquestPstageOpponentTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_day_quest_puzzle_stage_id: 1, mst_puzzle_opponent_id: 2, level: 3, wave_number: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstDquestPstageOpponentViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_day_quest_puzzle_stage_id).to eq use_case_data.mst_day_quest_puzzle_stage_id
      expect(view_model.mst_puzzle_opponent_id).to eq use_case_data.mst_puzzle_opponent_id
      expect(view_model.level).to eq use_case_data.level
      expect(view_model.wave_number).to eq use_case_data.wave_number
    end
  end
end
