require 'rails_helper'

RSpec.describe "OprEventRankingRewardTranslator" do
  subject { OprEventRankingRewardTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_event_id: 0, prize: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprEventRankingRewardViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_event_id).to eq use_case_data.opr_event_id
      expect(view_model.prize).to eq use_case_data.prize
    end
  end
end
