require 'rails_helper'

RSpec.describe "MstAppealActionTranslator" do
  subject { MstAppealActionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, mst_appeal_id: 0, level: 1, description_format: 2, primary_type: 3, power_percentage: 4, absorption_percentage: 5, secondary_type: 6, bit_target: 7, character_target: 8, angry: 9, joy: 10, sad: 11, happy: 12, action_value: 13, duration: 14, debuff_probability: 15)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstAppealActionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.mst_appeal_id).to eq use_case_data.mst_appeal_id
      expect(view_model.level).to eq use_case_data.level
      expect(view_model.description_format).to eq use_case_data.description_format
      expect(view_model.primary_type).to eq use_case_data.primary_type
      expect(view_model.power_percentage).to eq use_case_data.power_percentage
      expect(view_model.absorption_percentage).to eq use_case_data.absorption_percentage
      expect(view_model.secondary_type).to eq use_case_data.secondary_type
      expect(view_model.bit_target).to eq use_case_data.bit_target
      expect(view_model.character_target).to eq use_case_data.character_target
      expect(view_model.angry).to eq use_case_data.angry
      expect(view_model.joy).to eq use_case_data.joy
      expect(view_model.sad).to eq use_case_data.sad
      expect(view_model.happy).to eq use_case_data.happy
      expect(view_model.action_value).to eq use_case_data.action_value
      expect(view_model.duration).to eq use_case_data.duration
      expect(view_model.debuff_probability).to eq use_case_data.debuff_probability
    end
  end
end
