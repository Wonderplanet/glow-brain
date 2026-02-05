require 'rails_helper'

RSpec.describe "PuzzleSessionTranslator" do
  subject { PuzzleSessionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_or_opr_puzzle_stage_id: 1, musical_unit: 2, puzzle_guest: 3, seed: 4, songs: 5, consume_ap_percentage: 6, obtain_tp_percentage: 7, user_exp_percentage: 8, drop_percentage: 9)}

    it do
      view_model = subject
      expect(view_model.is_a?(PuzzleSessionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_or_opr_puzzle_stage_id).to eq use_case_data.mst_or_opr_puzzle_stage_id
      expect(view_model.musical_unit).to eq use_case_data.musical_unit
      expect(view_model.puzzle_guest).to eq use_case_data.puzzle_guest
      expect(view_model.seed).to eq use_case_data.seed
      expect(view_model.songs).to eq use_case_data.songs
      expect(view_model.consume_ap_percentage).to eq use_case_data.consume_ap_percentage
      expect(view_model.obtain_tp_percentage).to eq use_case_data.obtain_tp_percentage
      expect(view_model.user_exp_percentage).to eq use_case_data.user_exp_percentage
      expect(view_model.drop_percentage).to eq use_case_data.drop_percentage
    end
  end
end
