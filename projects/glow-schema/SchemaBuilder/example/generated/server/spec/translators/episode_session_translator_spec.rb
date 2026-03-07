require 'rails_helper'

RSpec.describe "EpisodeSessionTranslator" do
  subject { EpisodeSessionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, category: 0, episode_id: 1, mst_episode_id: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(EpisodeSessionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.category).to eq use_case_data.category
      expect(view_model.episode_id).to eq use_case_data.episode_id
      expect(view_model.mst_episode_id).to eq use_case_data.mst_episode_id
    end
  end
end
