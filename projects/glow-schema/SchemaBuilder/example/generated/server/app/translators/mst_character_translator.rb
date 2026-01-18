class MstCharacterTranslator
  def self.translate(mst_character_model)
    view_model = MstCharacterViewModel.new
    view_model.id = mst_character_model.id
    view_model.name = mst_character_model.name
    view_model.asset_key = mst_character_model.asset_key
    view_model.voice_actor_name = mst_character_model.voice_actor_name
    view_model.mst_main_artist_group_id = mst_character_model.mst_main_artist_group_id
    view_model.description = mst_character_model.description
    view_model.serial_number = mst_character_model.serial_number
    view_model.life_span = mst_character_model.life_span
    view_model.birth_day = mst_character_model.birth_day
    view_model.birth_month = mst_character_model.birth_month
    view_model.favorite = mst_character_model.favorite
    view_model.dislike = mst_character_model.dislike
    view_model.height = mst_character_model.height
    view_model.weight = mst_character_model.weight
    view_model.local_notification_message = mst_character_model.local_notification_message
    view_model
  end
end
