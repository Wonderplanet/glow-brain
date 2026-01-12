require 'rails_helper'

RSpec.describe "StepupGachaTranslator" do
  subject { StepupGachaTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_stepup_gacha_id: 0, current_step_number: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(StepupGachaViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_stepup_gacha_id).to eq use_case_data.opr_stepup_gacha_id
      expect(view_model.current_step_number).to eq use_case_data.current_step_number
    end
  end
end
