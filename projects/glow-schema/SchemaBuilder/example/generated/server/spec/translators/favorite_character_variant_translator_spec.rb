require 'rails_helper'

RSpec.describe "FavoriteCharacterVariantTranslator" do
  subject { FavoriteCharacterVariantTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, main_favorite_character_variant_id: 0, sub_favorite_character_variant_id1: 1, sub_favorite_character_variant_id2: 2, sub_favorite_character_variant_id3: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(FavoriteCharacterVariantViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.main_favorite_character_variant_id).to eq use_case_data.main_favorite_character_variant_id
      expect(view_model.sub_favorite_character_variant_id1).to eq use_case_data.sub_favorite_character_variant_id1
      expect(view_model.sub_favorite_character_variant_id2).to eq use_case_data.sub_favorite_character_variant_id2
      expect(view_model.sub_favorite_character_variant_id3).to eq use_case_data.sub_favorite_character_variant_id3
    end
  end
end
