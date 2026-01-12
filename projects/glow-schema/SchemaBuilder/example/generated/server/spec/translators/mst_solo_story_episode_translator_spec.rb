require 'rails_helper'

RSpec.describe "MstSoloStoryEpisodeTranslator" do
  subject { MstSoloStoryEpisodeTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_solo_story_chapter_id: 1, episode_number: 2, name: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstSoloStoryEpisodeViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_solo_story_chapter_id).to eq use_case_data.mst_solo_story_chapter_id
      expect(view_model.episode_number).to eq use_case_data.episode_number
      expect(view_model.name).to eq use_case_data.name
    end
  end
end
