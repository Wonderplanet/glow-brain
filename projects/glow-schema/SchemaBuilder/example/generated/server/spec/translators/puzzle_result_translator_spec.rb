require 'rails_helper'

RSpec.describe "PuzzleResultTranslator" do
  subject { PuzzleResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, puzzle_session_id: 0, obtained_tp: 1, drop_sets: 2, before_user: 3, after_user: 4, before_characters: 5, after_characters: 6, puzzle_result_unison_points: 7, sent_present_box_flg: 8)}

    it do
      view_model = subject
      expect(view_model.is_a?(PuzzleResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.puzzle_session_id).to eq use_case_data.puzzle_session_id
      expect(view_model.obtained_tp).to eq use_case_data.obtained_tp
      expect(view_model.drop_sets).to eq use_case_data.drop_sets
      expect(view_model.before_user).to eq use_case_data.before_user
      expect(view_model.after_user).to eq use_case_data.after_user
      expect(view_model.before_characters).to eq use_case_data.before_characters
      expect(view_model.after_characters).to eq use_case_data.after_characters
      expect(view_model.puzzle_result_unison_points).to eq use_case_data.puzzle_result_unison_points
      expect(view_model.sent_present_box_flg).to eq use_case_data.sent_present_box_flg
    end
  end
end
