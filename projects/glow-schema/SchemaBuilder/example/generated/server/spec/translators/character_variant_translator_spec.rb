require 'rails_helper'

RSpec.describe "CharacterVariantTranslator" do
  subject { CharacterVariantTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_character_variant_id: 1, level: 2, exp_point: 3, limit_break_level: 4, limit_break_max_level: 5, increase_hp_value: 6, increase_performance_value: 7, increase_heal_value: 8, appeal_level: 9, support_skill_level: 10, graphic_released: 11, current_graphic: 12, obtained_at: 13)}

    it do
      view_model = subject
      expect(view_model.is_a?(CharacterVariantViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_character_variant_id).to eq use_case_data.mst_character_variant_id
      expect(view_model.level).to eq use_case_data.level
      expect(view_model.exp_point).to eq use_case_data.exp_point
      expect(view_model.limit_break_level).to eq use_case_data.limit_break_level
      expect(view_model.limit_break_max_level).to eq use_case_data.limit_break_max_level
      expect(view_model.increase_hp_value).to eq use_case_data.increase_hp_value
      expect(view_model.increase_performance_value).to eq use_case_data.increase_performance_value
      expect(view_model.increase_heal_value).to eq use_case_data.increase_heal_value
      expect(view_model.appeal_level).to eq use_case_data.appeal_level
      expect(view_model.support_skill_level).to eq use_case_data.support_skill_level
      expect(view_model.graphic_released).to eq use_case_data.graphic_released
      expect(view_model.current_graphic).to eq use_case_data.current_graphic
      expect(view_model.obtained_at).to eq use_case_data.obtained_at
    end
  end
end
