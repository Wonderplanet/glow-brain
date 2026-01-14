require 'rails_helper'

RSpec.describe "EventStoryReleaseResultTranslator" do
  subject { EventStoryReleaseResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, event_story_episode: 0, consumed_crystal: 1, consumed_item: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(EventStoryReleaseResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.event_story_episode).to eq use_case_data.event_story_episode
      expect(view_model.consumed_crystal).to eq use_case_data.consumed_crystal
      expect(view_model.consumed_item).to eq use_case_data.consumed_item
    end
  end
end
