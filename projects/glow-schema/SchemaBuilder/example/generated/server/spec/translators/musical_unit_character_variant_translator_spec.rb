require 'rails_helper'

RSpec.describe "MusicalUnitCharacterVariantTranslator" do
  subject { MusicalUnitCharacterVariantTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, number: 0, musical_unit_id: 1, main_character_variant_id: 2, support_character_variant_id1: 3, support_character_variant_id2: 4, support_character_variant_id3: 5)}

    it do
      view_model = subject
      expect(view_model.is_a?(MusicalUnitCharacterVariantViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.number).to eq use_case_data.number
      expect(view_model.musical_unit_id).to eq use_case_data.musical_unit_id
      expect(view_model.main_character_variant_id).to eq use_case_data.main_character_variant_id
      expect(view_model.support_character_variant_id1).to eq use_case_data.support_character_variant_id1
      expect(view_model.support_character_variant_id2).to eq use_case_data.support_character_variant_id2
      expect(view_model.support_character_variant_id3).to eq use_case_data.support_character_variant_id3
    end
  end
end
