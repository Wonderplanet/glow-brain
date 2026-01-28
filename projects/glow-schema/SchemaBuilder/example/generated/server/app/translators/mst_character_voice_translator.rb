class MstCharacterVoiceTranslator
  def self.translate(mst_character_voice_model)
    view_model = MstCharacterVoiceViewModel.new
    view_model.id = mst_character_voice_model.id
    view_model.mst_character_id = mst_character_voice_model.mst_character_id
    view_model.name = mst_character_voice_model.name
    view_model.text = mst_character_voice_model.text
    view_model.voice_key = mst_character_voice_model.voice_key
    view_model.list_display_type = mst_character_voice_model.list_display_type
    view_model.release_condition = mst_character_voice_model.release_condition
    view_model.release_at = mst_character_voice_model.release_at
    view_model.releasable_start_at = mst_character_voice_model.releasable_start_at
    view_model.releasable_end_at = mst_character_voice_model.releasable_end_at
    view_model.sort_number = mst_character_voice_model.sort_number
    view_model
  end
end
