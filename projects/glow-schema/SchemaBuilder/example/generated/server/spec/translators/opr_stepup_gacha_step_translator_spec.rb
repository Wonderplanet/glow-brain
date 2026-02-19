require 'rails_helper'

RSpec.describe "OprStepupGachaStepTranslator" do
  subject { OprStepupGachaStepTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_stepup_gacha_id: 0, step_number: 1, primary_payment_amount: 2, primary_draw_count: 3, description: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprStepupGachaStepViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_stepup_gacha_id).to eq use_case_data.opr_stepup_gacha_id
      expect(view_model.step_number).to eq use_case_data.step_number
      expect(view_model.primary_payment_amount).to eq use_case_data.primary_payment_amount
      expect(view_model.primary_draw_count).to eq use_case_data.primary_draw_count
      expect(view_model.description).to eq use_case_data.description
    end
  end
end
