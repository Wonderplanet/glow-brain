require 'rails_helper'

RSpec.describe "SoloLiveRankingTranslator" do
  subject { SoloLiveRankingTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, profiles: 0, total_count: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(SoloLiveRankingViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.profiles).to eq use_case_data.profiles
      expect(view_model.total_count).to eq use_case_data.total_count
    end
  end
end
