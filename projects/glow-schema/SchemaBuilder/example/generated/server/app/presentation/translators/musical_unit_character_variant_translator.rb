class MusicalUnitCharacterVariantTranslator
  def self.translate(musical_unit_character_variant_model)
    view_model = MusicalUnitCharacterVariantViewModel.new
    view_model.number = musical_unit_character_variant_model.number
    view_model.musical_unit_id = musical_unit_character_variant_model.musical_unit_id
    view_model.main_character_variant_id = musical_unit_character_variant_model.main_character_variant_id
    view_model.support_character_variant_id1 = musical_unit_character_variant_model.support_character_variant_id1
    view_model.support_character_variant_id2 = musical_unit_character_variant_model.support_character_variant_id2
    view_model.support_character_variant_id3 = musical_unit_character_variant_model.support_character_variant_id3
    view_model
  end
end
