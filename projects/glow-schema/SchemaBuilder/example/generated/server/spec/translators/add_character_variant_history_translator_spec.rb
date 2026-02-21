require 'rails_helper'

RSpec.describe "AddCharacterVariantHistoryTranslator" do
  subject { AddCharacterVariantHistoryTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, character_variant_id: 0, mst_character_variant_id: 1, limit_break_level: 2, memory_duplicate_point: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(AddCharacterVariantHistoryViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.character_variant_id).to eq use_case_data.character_variant_id
      expect(view_model.mst_character_variant_id).to eq use_case_data.mst_character_variant_id
      expect(view_model.limit_break_level).to eq use_case_data.limit_break_level
      expect(view_model.memory_duplicate_point).to eq use_case_data.memory_duplicate_point
    end
  end
end
