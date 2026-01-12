class MusicalUnitTranslator
  def self.translate(musical_unit_model)
    view_model = MusicalUnitViewModel.new
    view_model.id = musical_unit_model.id
    view_model.name = musical_unit_model.name
    view_model.number = musical_unit_model.number
    view_model.leader_musical_unit_character_variant_number = musical_unit_model.leader_musical_unit_character_variant_number
    view_model.musical_unit_character_variants = musical_unit_model.musical_unit_character_variants
    view_model
  end
end
