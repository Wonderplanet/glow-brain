require 'rails_helper'

RSpec.describe "OprEventPointRewardTranslator" do
  subject { OprEventPointRewardTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_event_id: 0, target_point: 1, extended_prize: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprEventPointRewardViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_event_id).to eq use_case_data.opr_event_id
      expect(view_model.target_point).to eq use_case_data.target_point
      expect(view_model.extended_prize).to eq use_case_data.extended_prize
    end
  end
end
