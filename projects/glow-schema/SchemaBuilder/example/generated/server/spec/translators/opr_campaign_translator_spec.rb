require 'rails_helper'

RSpec.describe "OprCampaignTranslator" do
  subject { OprCampaignTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, target: 1, feature: 2, percentage: 3, min_rank: 4, max_rank: 5, start_at: 6, end_at: 7)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprCampaignViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.target).to eq use_case_data.target
      expect(view_model.feature).to eq use_case_data.feature
      expect(view_model.percentage).to eq use_case_data.percentage
      expect(view_model.min_rank).to eq use_case_data.min_rank
      expect(view_model.max_rank).to eq use_case_data.max_rank
      expect(view_model.start_at).to eq use_case_data.start_at
      expect(view_model.end_at).to eq use_case_data.end_at
    end
  end
end
