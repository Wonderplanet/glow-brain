class FavoriteCharacterVariantUpdateResultTranslator
  def self.translate(favorite_character_variant_update_result_model)
    view_model = FavoriteCharacterVariantUpdateResultViewModel.new
    view_model.updated_mission = favorite_character_variant_update_result_model.updated_mission
    view_model
  end
end
