require 'rails_helper'

RSpec.describe "GroupStoryEpisodeTranslator" do
  subject { GroupStoryEpisodeTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_group_story_episode_id: 1, play_count: 2, clear_count: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(GroupStoryEpisodeViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_group_story_episode_id).to eq use_case_data.mst_group_story_episode_id
      expect(view_model.play_count).to eq use_case_data.play_count
      expect(view_model.clear_count).to eq use_case_data.clear_count
    end
  end
end
