require 'rails_helper'

RSpec.describe "MstLoginBonusCharacterVariantTranslator" do
  subject { MstLoginBonusCharacterVariantTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, mst_character_variant_id1: 0, mst_character_variant_id2: 1, login_bonus_category: 2, character1_voice_key: 3, character2_voice_key: 4, character1_animation_name: 5, character2_animation_name: 6, wait_time: 7, text1: 8, text2: 9)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstLoginBonusCharacterVariantViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.mst_character_variant_id1).to eq use_case_data.mst_character_variant_id1
      expect(view_model.mst_character_variant_id2).to eq use_case_data.mst_character_variant_id2
      expect(view_model.login_bonus_category).to eq use_case_data.login_bonus_category
      expect(view_model.character1_voice_key).to eq use_case_data.character1_voice_key
      expect(view_model.character2_voice_key).to eq use_case_data.character2_voice_key
      expect(view_model.character1_animation_name).to eq use_case_data.character1_animation_name
      expect(view_model.character2_animation_name).to eq use_case_data.character2_animation_name
      expect(view_model.wait_time).to eq use_case_data.wait_time
      expect(view_model.text1).to eq use_case_data.text1
      expect(view_model.text2).to eq use_case_data.text2
    end
  end
end
