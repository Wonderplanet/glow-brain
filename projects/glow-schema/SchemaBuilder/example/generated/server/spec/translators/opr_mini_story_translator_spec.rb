require 'rails_helper'

RSpec.describe "OprMiniStoryTranslator" do
  subject { OprMiniStoryTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, title: 1, asset_key: 2, start_at: 3, end_at: 4, top_priority: 5, release_at: 6)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprMiniStoryViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.title).to eq use_case_data.title
      expect(view_model.asset_key).to eq use_case_data.asset_key
      expect(view_model.start_at).to eq use_case_data.start_at
      expect(view_model.end_at).to eq use_case_data.end_at
      expect(view_model.top_priority).to eq use_case_data.top_priority
      expect(view_model.release_at).to eq use_case_data.release_at
    end
  end
end
