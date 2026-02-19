require 'rails_helper'

RSpec.describe "OprEventNquestPstageSongTranslator" do
  subject { OprEventNquestPstageSongTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, opr_event_normal_quest_puzzle_stage_id: 1, song_number: 2, mst_music_id: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprEventNquestPstageSongViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.opr_event_normal_quest_puzzle_stage_id).to eq use_case_data.opr_event_normal_quest_puzzle_stage_id
      expect(view_model.song_number).to eq use_case_data.song_number
      expect(view_model.mst_music_id).to eq use_case_data.mst_music_id
    end
  end
end
