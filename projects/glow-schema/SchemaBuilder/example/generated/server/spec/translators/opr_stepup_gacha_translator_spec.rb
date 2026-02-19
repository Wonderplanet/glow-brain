require 'rails_helper'

RSpec.describe "OprStepupGachaTranslator" do
  subject { OprStepupGachaTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, name: 1, asset_key: 2, start_at: 3, end_at: 4, sort_number: 5, banner_path: 6, stepup_gacha_payment_type: 7)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprStepupGachaViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.asset_key).to eq use_case_data.asset_key
      expect(view_model.start_at).to eq use_case_data.start_at
      expect(view_model.end_at).to eq use_case_data.end_at
      expect(view_model.sort_number).to eq use_case_data.sort_number
      expect(view_model.banner_path).to eq use_case_data.banner_path
      expect(view_model.stepup_gacha_payment_type).to eq use_case_data.stepup_gacha_payment_type
    end
  end
end
