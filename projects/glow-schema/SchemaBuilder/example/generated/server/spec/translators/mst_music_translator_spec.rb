require 'rails_helper'

RSpec.describe "MstMusicTranslator" do
  subject { MstMusicTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, track_number: 1, title: 2, bpm: 3, artist_name: 4, composer_name: 5, arranger_name: 6, lyric_writer_name: 7, mst_artist_group_id: 8, asset_key: 9, effect_description: 10, effect_target: 11, hp_effect: 12, performance_effect: 13, heal_effect: 14)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstMusicViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.track_number).to eq use_case_data.track_number
      expect(view_model.title).to eq use_case_data.title
      expect(view_model.bpm).to eq use_case_data.bpm
      expect(view_model.artist_name).to eq use_case_data.artist_name
      expect(view_model.composer_name).to eq use_case_data.composer_name
      expect(view_model.arranger_name).to eq use_case_data.arranger_name
      expect(view_model.lyric_writer_name).to eq use_case_data.lyric_writer_name
      expect(view_model.mst_artist_group_id).to eq use_case_data.mst_artist_group_id
      expect(view_model.asset_key).to eq use_case_data.asset_key
      expect(view_model.effect_description).to eq use_case_data.effect_description
      expect(view_model.effect_target).to eq use_case_data.effect_target
      expect(view_model.hp_effect).to eq use_case_data.hp_effect
      expect(view_model.performance_effect).to eq use_case_data.performance_effect
      expect(view_model.heal_effect).to eq use_case_data.heal_effect
    end
  end
end
