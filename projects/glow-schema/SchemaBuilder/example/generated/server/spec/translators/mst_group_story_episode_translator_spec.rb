require 'rails_helper'

RSpec.describe "MstGroupStoryEpisodeTranslator" do
  subject { MstGroupStoryEpisodeTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_group_story_chapter_id: 1, episode_number: 2, name: 3, point_consume_mst_character_id1: 4, point_consume_mst_character_id2: 5, point_consume_mst_character_id3: 6, point_consume_mst_character_id4: 7, required_unison_point: 8, dependency_mst_group_story_episode_id: 9)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstGroupStoryEpisodeViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_group_story_chapter_id).to eq use_case_data.mst_group_story_chapter_id
      expect(view_model.episode_number).to eq use_case_data.episode_number
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.point_consume_mst_character_id1).to eq use_case_data.point_consume_mst_character_id1
      expect(view_model.point_consume_mst_character_id2).to eq use_case_data.point_consume_mst_character_id2
      expect(view_model.point_consume_mst_character_id3).to eq use_case_data.point_consume_mst_character_id3
      expect(view_model.point_consume_mst_character_id4).to eq use_case_data.point_consume_mst_character_id4
      expect(view_model.required_unison_point).to eq use_case_data.required_unison_point
      expect(view_model.dependency_mst_group_story_episode_id).to eq use_case_data.dependency_mst_group_story_episode_id
    end
  end
end
