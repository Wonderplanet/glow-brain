require 'rails_helper'

RSpec.describe "FavoriteMstCharacterVariantTranslator" do
  subject { FavoriteMstCharacterVariantTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, main_favorite_mst_character_variant_id: 0, main_current_graphic: 1, sub_favorite_mst_character_variant_id1: 2, sub1_current_graphic: 3, sub_favorite_mst_character_variant_id2: 4, sub2_current_graphic: 5, sub_favorite_mst_character_variant_id3: 6, sub3_current_graphic: 7)}

    it do
      view_model = subject
      expect(view_model.is_a?(FavoriteMstCharacterVariantViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.main_favorite_mst_character_variant_id).to eq use_case_data.main_favorite_mst_character_variant_id
      expect(view_model.main_current_graphic).to eq use_case_data.main_current_graphic
      expect(view_model.sub_favorite_mst_character_variant_id1).to eq use_case_data.sub_favorite_mst_character_variant_id1
      expect(view_model.sub1_current_graphic).to eq use_case_data.sub1_current_graphic
      expect(view_model.sub_favorite_mst_character_variant_id2).to eq use_case_data.sub_favorite_mst_character_variant_id2
      expect(view_model.sub2_current_graphic).to eq use_case_data.sub2_current_graphic
      expect(view_model.sub_favorite_mst_character_variant_id3).to eq use_case_data.sub_favorite_mst_character_variant_id3
      expect(view_model.sub3_current_graphic).to eq use_case_data.sub3_current_graphic
    end
  end
end
