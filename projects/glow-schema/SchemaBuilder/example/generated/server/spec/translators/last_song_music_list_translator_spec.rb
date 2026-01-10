require 'rails_helper'

RSpec.describe "LastSongMusicListTranslator" do
  subject { LastSongMusicListTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, is_random: 0, enabled: 1, mst_music_ids: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(LastSongMusicListViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.is_random).to eq use_case_data.is_random
      expect(view_model.enabled).to eq use_case_data.enabled
      expect(view_model.mst_music_ids).to eq use_case_data.mst_music_ids
    end
  end
end
