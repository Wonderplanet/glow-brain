require 'rails_helper'

RSpec.describe "SoloLivePuzzleSessionTranslator" do
  subject { SoloLivePuzzleSessionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, musical_unit: 1, puzzle_guest: 2, seed: 3, drop: 4, self_best_score: 5, turn_boost: 6, fever_boost: 7, appeal_bit_boost: 8, consume_ap_percentage: 9, obtain_tp_percentage: 10, user_exp_percentage: 11, drop_percentage: 12)}

    it do
      view_model = subject
      expect(view_model.is_a?(SoloLivePuzzleSessionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.musical_unit).to eq use_case_data.musical_unit
      expect(view_model.puzzle_guest).to eq use_case_data.puzzle_guest
      expect(view_model.seed).to eq use_case_data.seed
      expect(view_model.drop).to eq use_case_data.drop
      expect(view_model.self_best_score).to eq use_case_data.self_best_score
      expect(view_model.turn_boost).to eq use_case_data.turn_boost
      expect(view_model.fever_boost).to eq use_case_data.fever_boost
      expect(view_model.appeal_bit_boost).to eq use_case_data.appeal_bit_boost
      expect(view_model.consume_ap_percentage).to eq use_case_data.consume_ap_percentage
      expect(view_model.obtain_tp_percentage).to eq use_case_data.obtain_tp_percentage
      expect(view_model.user_exp_percentage).to eq use_case_data.user_exp_percentage
      expect(view_model.drop_percentage).to eq use_case_data.drop_percentage
    end
  end
end
