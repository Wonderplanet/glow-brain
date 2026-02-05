require 'rails_helper'

RSpec.describe "MusicalUnitTranslator" do
  subject { MusicalUnitTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, name: 1, number: 2, leader_musical_unit_character_variant_number: 3, musical_unit_character_variants: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(MusicalUnitViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.number).to eq use_case_data.number
      expect(view_model.leader_musical_unit_character_variant_number).to eq use_case_data.leader_musical_unit_character_variant_number
      expect(view_model.musical_unit_character_variants).to eq use_case_data.musical_unit_character_variants
    end
  end
end
