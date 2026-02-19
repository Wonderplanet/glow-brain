require 'rails_helper'

RSpec.describe "OprPointUpCharacterVariantTranslator" do
  subject { OprPointUpCharacterVariantTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_event_id: 0, mst_character_variant_id: 1, limit_break_count0_percentage: 2, limit_break_count1_percentage: 3, limit_break_count2_percentage: 4, limit_break_count3_percentage: 5)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprPointUpCharacterVariantViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_event_id).to eq use_case_data.opr_event_id
      expect(view_model.mst_character_variant_id).to eq use_case_data.mst_character_variant_id
      expect(view_model.limit_break_count0_percentage).to eq use_case_data.limit_break_count0_percentage
      expect(view_model.limit_break_count1_percentage).to eq use_case_data.limit_break_count1_percentage
      expect(view_model.limit_break_count2_percentage).to eq use_case_data.limit_break_count2_percentage
      expect(view_model.limit_break_count3_percentage).to eq use_case_data.limit_break_count3_percentage
    end
  end
end
