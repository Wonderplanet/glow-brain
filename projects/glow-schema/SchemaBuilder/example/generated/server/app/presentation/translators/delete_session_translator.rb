class DeleteSessionTranslator
  def self.translate(delete_session_model)
    view_model = DeleteSessionViewModel.new
    view_model.reward_type = delete_session_model.reward_type
    view_model.present_box = delete_session_model.present_box
    view_model.updated_mission = delete_session_model.updated_mission
    view_model.mst_released_music_ids = delete_session_model.mst_released_music_ids
    view_model
  end
end
