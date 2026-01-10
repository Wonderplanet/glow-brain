class CharacterUnisonPointTranslator
  def self.translate(character_unison_point_model)
    view_model = CharacterUnisonPointViewModel.new
    view_model.id = character_unison_point_model.id
    view_model.mst_character_id = character_unison_point_model.mst_character_id
    view_model.point = character_unison_point_model.point
    view_model
  end
end
