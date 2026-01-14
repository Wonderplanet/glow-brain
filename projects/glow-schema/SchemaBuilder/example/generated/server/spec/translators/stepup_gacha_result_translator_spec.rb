require 'rails_helper'

RSpec.describe "StepupGachaResultTranslator" do
  subject { StepupGachaResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, received: 0, received_histories: 1, stepup_gacha: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(StepupGachaResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.received).to eq use_case_data.received
      expect(view_model.received_histories).to eq use_case_data.received_histories
      expect(view_model.stepup_gacha).to eq use_case_data.stepup_gacha
    end
  end
end
