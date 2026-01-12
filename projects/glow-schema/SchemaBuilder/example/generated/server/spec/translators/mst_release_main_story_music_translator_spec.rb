require 'rails_helper'

RSpec.describe "MstReleaseMainStoryMusicTranslator" do
  subject { MstReleaseMainStoryMusicTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, mst_main_episode_id: 0, mst_music_id: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstReleaseMainStoryMusicViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.mst_main_episode_id).to eq use_case_data.mst_main_episode_id
      expect(view_model.mst_music_id).to eq use_case_data.mst_music_id
    end
  end
end
