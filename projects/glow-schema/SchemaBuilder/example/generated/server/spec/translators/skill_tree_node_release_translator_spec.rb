require 'rails_helper'

RSpec.describe "SkillTreeNodeReleaseTranslator" do
  subject { SkillTreeNodeReleaseTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, mst_character_variant_id: 0, last_released_bit_number: 1, flags: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(SkillTreeNodeReleaseViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.mst_character_variant_id).to eq use_case_data.mst_character_variant_id
      expect(view_model.last_released_bit_number).to eq use_case_data.last_released_bit_number
      expect(view_model.flags).to eq use_case_data.flags
    end
  end
end
