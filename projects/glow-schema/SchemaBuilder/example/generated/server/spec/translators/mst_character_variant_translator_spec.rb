require 'rails_helper'

RSpec.describe "MstCharacterVariantTranslator" do
  subject { MstCharacterVariantTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_character_id: 1, name: 2, asset_key: 3, rarity: 4, emotion_element: 5, mst_leader_skill_id: 6, mst_appeal_id: 7, mst_support_skill_id: 8, special_attack_speech: 9, belong_unit_name: 10, hp_coef: 11, performance_coef: 12, heal_coef: 13, label_coef: 14, released_increase_hp_value: 15, released_increase_performance_value: 16, released_increase_heal_value: 17, has_signature: 18)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstCharacterVariantViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_character_id).to eq use_case_data.mst_character_id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.asset_key).to eq use_case_data.asset_key
      expect(view_model.rarity).to eq use_case_data.rarity
      expect(view_model.emotion_element).to eq use_case_data.emotion_element
      expect(view_model.mst_leader_skill_id).to eq use_case_data.mst_leader_skill_id
      expect(view_model.mst_appeal_id).to eq use_case_data.mst_appeal_id
      expect(view_model.mst_support_skill_id).to eq use_case_data.mst_support_skill_id
      expect(view_model.special_attack_speech).to eq use_case_data.special_attack_speech
      expect(view_model.belong_unit_name).to eq use_case_data.belong_unit_name
      expect(view_model.hp_coef).to eq use_case_data.hp_coef
      expect(view_model.performance_coef).to eq use_case_data.performance_coef
      expect(view_model.heal_coef).to eq use_case_data.heal_coef
      expect(view_model.label_coef).to eq use_case_data.label_coef
      expect(view_model.released_increase_hp_value).to eq use_case_data.released_increase_hp_value
      expect(view_model.released_increase_performance_value).to eq use_case_data.released_increase_performance_value
      expect(view_model.released_increase_heal_value).to eq use_case_data.released_increase_heal_value
      expect(view_model.has_signature).to eq use_case_data.has_signature
    end
  end
end
