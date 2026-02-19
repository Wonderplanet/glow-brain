class CharacterVariantGraphicResultTranslator
  def self.translate(character_variant_graphic_result_model)
    view_model = CharacterVariantGraphicResultViewModel.new
    view_model.character_variant = character_variant_graphic_result_model.character_variant
    view_model.updated_mission = character_variant_graphic_result_model.updated_mission
    view_model
  end
end
