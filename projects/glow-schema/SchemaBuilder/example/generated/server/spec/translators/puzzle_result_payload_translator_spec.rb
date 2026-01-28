require 'rails_helper'

RSpec.describe "PuzzleResultPayloadTranslator" do
  subject { PuzzleResultPayloadTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, defeated_opponent_ids: 0, character_unison_points: 1, song_done_turns: 2, check_hash: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(PuzzleResultPayloadViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.defeated_opponent_ids).to eq use_case_data.defeated_opponent_ids
      expect(view_model.character_unison_points).to eq use_case_data.character_unison_points
      expect(view_model.song_done_turns).to eq use_case_data.song_done_turns
      expect(view_model.check_hash).to eq use_case_data.check_hash
    end
  end
end
