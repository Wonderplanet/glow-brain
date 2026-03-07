require 'rails_helper'

RSpec.describe "MstEventStoryCharacterTranslator" do
  subject { MstEventStoryCharacterTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_event_story_episode_id: 1, mst_character_id: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstEventStoryCharacterViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_event_story_episode_id).to eq use_case_data.mst_event_story_episode_id
      expect(view_model.mst_character_id).to eq use_case_data.mst_character_id
    end
  end
end
