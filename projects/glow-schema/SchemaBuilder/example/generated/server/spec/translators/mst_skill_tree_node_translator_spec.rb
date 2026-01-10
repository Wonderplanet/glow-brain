require 'rails_helper'

RSpec.describe "MstSkillTreeNodeTranslator" do
  subject { MstSkillTreeNodeTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_character_variant_id: 1, bit_number: 2, parent_bit_number: 3, node_type: 4, required_level: 5, horizontal_position: 6, vertical_position: 7, increase_hp_value: 8, increase_performance_value: 9, increase_heal_value: 10, voice_key: 11)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstSkillTreeNodeViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_character_variant_id).to eq use_case_data.mst_character_variant_id
      expect(view_model.bit_number).to eq use_case_data.bit_number
      expect(view_model.parent_bit_number).to eq use_case_data.parent_bit_number
      expect(view_model.node_type).to eq use_case_data.node_type
      expect(view_model.required_level).to eq use_case_data.required_level
      expect(view_model.horizontal_position).to eq use_case_data.horizontal_position
      expect(view_model.vertical_position).to eq use_case_data.vertical_position
      expect(view_model.increase_hp_value).to eq use_case_data.increase_hp_value
      expect(view_model.increase_performance_value).to eq use_case_data.increase_performance_value
      expect(view_model.increase_heal_value).to eq use_case_data.increase_heal_value
      expect(view_model.voice_key).to eq use_case_data.voice_key
    end
  end
end
