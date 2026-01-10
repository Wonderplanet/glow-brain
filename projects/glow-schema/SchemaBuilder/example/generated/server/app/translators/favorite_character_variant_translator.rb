class FavoriteCharacterVariantTranslator
  def self.translate(favorite_character_variant_model)
    view_model = FavoriteCharacterVariantViewModel.new
    view_model.main_favorite_character_variant_id = favorite_character_variant_model.main_favorite_character_variant_id
    view_model.sub_favorite_character_variant_id1 = favorite_character_variant_model.sub_favorite_character_variant_id1
    view_model.sub_favorite_character_variant_id2 = favorite_character_variant_model.sub_favorite_character_variant_id2
    view_model.sub_favorite_character_variant_id3 = favorite_character_variant_model.sub_favorite_character_variant_id3
    view_model
  end
end
