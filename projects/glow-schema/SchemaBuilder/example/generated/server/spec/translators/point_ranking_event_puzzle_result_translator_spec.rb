require 'rails_helper'

RSpec.describe "PointRankingEventPuzzleResultTranslator" do
  subject { PointRankingEventPuzzleResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, puzzle_result: 0, puzzle_event_result: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(PointRankingEventPuzzleResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.puzzle_result).to eq use_case_data.puzzle_result
      expect(view_model.puzzle_event_result).to eq use_case_data.puzzle_event_result
    end
  end
end
