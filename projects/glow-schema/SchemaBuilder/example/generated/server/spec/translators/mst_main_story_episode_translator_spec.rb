require 'rails_helper'

RSpec.describe "MstMainStoryEpisodeTranslator" do
  subject { MstMainStoryEpisodeTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, episode_number: 1, mst_main_story_chapter_id: 2, name: 3, necessary_user_rank: 4, dependency_mst_main_quest_id: 5, is_movie: 6, release_at: 7)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstMainStoryEpisodeViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.episode_number).to eq use_case_data.episode_number
      expect(view_model.mst_main_story_chapter_id).to eq use_case_data.mst_main_story_chapter_id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.necessary_user_rank).to eq use_case_data.necessary_user_rank
      expect(view_model.dependency_mst_main_quest_id).to eq use_case_data.dependency_mst_main_quest_id
      expect(view_model.is_movie).to eq use_case_data.is_movie
      expect(view_model.release_at).to eq use_case_data.release_at
    end
  end
end
