class CharacterVoiceTranslator
  def self.translate(character_voice_model)
    view_model = CharacterVoiceViewModel.new
    view_model.mst_character_id = character_voice_model.mst_character_id
    view_model.flags = character_voice_model.flags
    view_model
  end
end
