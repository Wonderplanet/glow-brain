class CharacterVariantVoiceTranslator
  def self.translate(character_variant_voice_model)
    view_model = CharacterVariantVoiceViewModel.new
    view_model.mst_character_variant_id = character_variant_voice_model.mst_character_variant_id
    view_model.flags = character_variant_voice_model.flags
    view_model
  end
end
