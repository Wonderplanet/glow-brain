require 'rails_helper'

RSpec.describe "AutoGenerateGachaSettingTranslator" do
  subject { AutoGenerateGachaSettingTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, gacha_generate_flg: 0, sort_number: 1, with_cr_only: 2, pickup_percentage: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(AutoGenerateGachaSettingViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.gacha_generate_flg).to eq use_case_data.gacha_generate_flg
      expect(view_model.sort_number).to eq use_case_data.sort_number
      expect(view_model.with_cr_only).to eq use_case_data.with_cr_only
      expect(view_model.pickup_percentage).to eq use_case_data.pickup_percentage
    end
  end
end
