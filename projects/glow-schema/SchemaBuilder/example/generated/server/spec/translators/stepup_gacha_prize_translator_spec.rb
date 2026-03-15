require 'rails_helper'

RSpec.describe "StepupGachaPrizeTranslator" do
  subject { StepupGachaPrizeTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, prize: 0, percentage: 1, pickup: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(StepupGachaPrizeViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.prize).to eq use_case_data.prize
      expect(view_model.percentage).to eq use_case_data.percentage
      expect(view_model.pickup).to eq use_case_data.pickup
    end
  end
end
