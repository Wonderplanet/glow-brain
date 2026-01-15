require 'rails_helper'

RSpec.describe "SoloLiveRankingRewardTranslator" do
  subject { SoloLiveRankingRewardTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, rank_from: 0, rank_to: 1, border: 2, is_lucky_number: 3, prizes: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(SoloLiveRankingRewardViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.rank_from).to eq use_case_data.rank_from
      expect(view_model.rank_to).to eq use_case_data.rank_to
      expect(view_model.border).to eq use_case_data.border
      expect(view_model.is_lucky_number).to eq use_case_data.is_lucky_number
      expect(view_model.prizes).to eq use_case_data.prizes
    end
  end
end
